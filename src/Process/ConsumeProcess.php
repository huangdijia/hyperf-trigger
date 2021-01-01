<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger\Process;

use Huangdijia\Trigger\Subscriber\HeartbeatSubscriber;
use Huangdijia\Trigger\Subscriber\TriggerSubscriber;
use Huangdijia\Trigger\SubscriberManager;
use Huangdijia\Trigger\SubscriberManagerFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use MySQLReplication\BinLog\BinLogCurrent;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class ConsumeProcess extends AbstractProcess
{
    /**
     * Connection.
     * @var string
     */
    protected $connection = 'default';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var SubscriberManager
     */
    protected $subscriberManager;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $key = 'trigger.' . $this->connection;

        /** @var SubscriberManagerFactory $subscriberManagerFactory */
        $subscriberManagerFactory = $container->get(SubscriberManagerFactory::class);
        $this->subscriberManager = $subscriberManagerFactory->create($this->connection);

        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        if (! $config->has($key)) {
            throw new RuntimeException('config ' . $key . ' is undefined.');
        }

        $this->cache = $container->get(CacheInterface::class);
        $this->config = $config->get($key);
        $this->name = "trigger.{$this->connection}";
        $this->nums = $this->config['processes'] ?? 1;
    }

    public function handle(): void
    {
        // $this->logger->info(json_encode($this->config, JSON_PRETTY_PRINT));

        $configBuilder = new ConfigBuilder();
        $configBuilder->withUser($this->config['user'] ?? 'root')
            ->withHost($this->config['host'] ?? '127.0.0.1')
            ->withPassword($this->config['password'] ?? 'root')
            ->withPort((int) $this->config['port'] ?? 3306)
            ->withSlaveId($this->getSlaveId())
            ->withHeartbeatPeriod((float) $this->config['heartbeat_period'] ?? 3)
            ->withDatabasesOnly((array) $this->config['databases_only'] ?? [])
            ->withtablesOnly((array) $this->config['tables_only'] ?? []);

        if ($binlog = $this->getLatestBinlog()) {
            $configBuilder->withBinLogFileName($binlog->getBinFileName())
                ->withBinLogPosition($binlog->getBinLogPosition());
            $this->logger->info(json_encode($this->config + ['binlog_filename' => $binlog->getBinFileName(), 'binlog_position' => $binlog->getBinLogPosition()], JSON_PRETTY_PRINT));
        }

        $binLogStream = new MySQLReplicationFactory($configBuilder->build());

        $subscribers = $this->subscriberManager->get($this->connection) + [
            HeartbeatSubscriber::class,
            TriggerSubscriber::class,
        ];

        foreach ($subscribers as $subscriber) {
            $binLogStream->registerSubscriber(new $subscriber($this->container, $this->connection));

            $this->logger->info(sprintf('[trigger.%s] %s registered.', $this->connection, $subscriber));
        }

        $binLogStream->run();
    }

    /**
     * @return null|BinLogCurrent
     */
    protected function getLatestBinlog()
    {
        $key = HeartbeatSubscriber::CACHE_KEY_PREFIX . $this->connection;

        return $this->cache->get($key, null);
    }

    /**
     * @throws RuntimeException
     */
    protected function getInternalIp(): string
    {
        $ips = swoole_get_local_ip();

        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }

        /** @var mixed|string $ip */
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }

        throw new RuntimeException('Can not get the internal IP.');
    }

    /**
     * @throws RuntimeException
     * @return int
     */
    protected function getSlaveId()
    {
        return (int) ip2long($this->getInternalIp());
    }
}

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

use Huangdijia\Trigger\Annotation\Trigger;
use Huangdijia\Trigger\Constact\ListenerInterface;
use Huangdijia\Trigger\ListenerManager;
use Huangdijia\Trigger\ListenerManagerFactory;
use Huangdijia\Trigger\Subscriber\EventSubscriber;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ConsumeProcess extends AbstractProcess
{
    /**
     * Connection.
     * @var string
     */
    protected $connection = 'default';

    /**
     * @var ListenerManager
     */
    protected $listenerManager;

    /**
     * @Inject
     * @var ListenerManagerFactory
     */
    protected $listenerManagerFactory;

    /**
     * @var array
     */
    protected $config;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $key = 'trigger.' . $this->connection;

        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);

        if (! $config->has($key)) {
            throw new RuntimeException('config ' . $key . ' is undefined.');
        }

        $this->listenerManager = $this->listenerManagerFactory->create($this->connection);
        $this->config = $config->get($key);
        $this->name = "trigger.{$this->connection}";
        $this->nums = $this->config['processes'] ?? 1;
    }

    public function handle(): void
    {
        // $this->logger->info(json_encode($this->config, JSON_PRETTY_PRINT));

        $this->registerListeners();

        $binLogStream = new MySQLReplicationFactory(
            (new ConfigBuilder())
                ->withUser($this->config['user'] ?? 'root')
                ->withHost($this->config['host'] ?? '127.0.0.1')
                ->withPassword($this->config['password'] ?? 'root')
                ->withPort((int) $this->config['port'] ?? 3306)
                ->withSlaveId($this->getSlaveId())
                ->withHeartbeatPeriod((float) $this->config['heartbeat_period'] ?? 2)
                ->withDatabasesOnly((array) $this->config['databases_only'] ?? [])
                ->withtablesOnly((array) $this->config['tables_only'] ?? [])
                ->build()
        );

        $binLogStream->registerSubscriber(new EventSubscriber($this->listenerManager, $this->config, $this->logger));

        $binLogStream->run();
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

        throw new \RuntimeException('Can not get the internal IP.');
    }

    private function registerListeners()
    {
        $listeners = $this->getAnnotationListeners();

        foreach ($listeners as $class => $properties) {
            if (! in_array(ListenerInterface::class, class_implements($class))) {
                $this->logger->warning(sprintf('%s is not implemented by %s.', $class, ListenerInterface::class));
                continue;
            }

            if ($properties->connection != $this->connection) {
                continue;
            }

            if (count($properties->listen) == 0) {
                $this->logger->warning(sprintf('%s\'s listen is empty.', $class, ListenerInterface::class));
                continue;
            }

            $this->listenerManager->register($properties->listen, $class);

            $this->logger->info(sprintf('[trigger.%s] %s registered.', $this->connection, $class), $properties->listen);
        }
    }

    /**
     * @return array
     */
    private function getAnnotationListeners()
    {
        return AnnotationCollector::getClassesByAnnotation(Trigger::class);
    }

    /**
     * @throws RuntimeException
     * @return int
     */
    private function getSlaveId()
    {
        return (int) ip2long($this->getInternalIp());
    }
}

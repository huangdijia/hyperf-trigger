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
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;
use RuntimeException;

class ConsumeProcess extends AbstractProcess
{
    /**
     * Connection.
     * @var string
     */
    protected $connection = 'default';

    /**
     * @Inject
     * @var ListenerManagerFactory
     */
    protected $listenerManagerFactory;

    /**
     * @var ListenerManager
     */
    protected $listenerManager;

    /**
     * @Inject
     * @var ConfigInterface
     */
    protected $config;

    public function handle(): void
    {
        $listenerManager = $this->listenerManagerFactory->create($this->connection);

        $configKey = 'trigger' . $this->connection;

        if ($this->config->has($configKey)) {
            throw new RuntimeException($configKey . ' is undefined.');
        }

        $config = $this->config->get($configKey);

        $this->registerListeners($listenerManager);

        $binLogStream = new MySQLReplicationFactory(
            (new ConfigBuilder())
                ->withUser($config['user'] ?? 'root')
                ->withHost($config['host'] ?? '127.0.0.1')
                ->withPassword($config['password'] ?? 'root')
                ->withPort((int) $config['port'] ?? 3306)
                ->withSlaveId($this->getSlaveId())
                ->withHeartbeatPeriod((float) $config['heartbeat_period'] ?? 2)
                ->withDatabasesOnly((array) $config['databases_only'] ?? [])
                ->withtablesOnly((array) $config['tables_only'] ?? [])
                ->build()
        );

        $binLogStream->registerSubscriber(new EventSubscriber($listenerManager));

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

    private function registerListeners(ListenerManager $listenerManager)
    {
        $listeners = $this->getAnnotationListeners();

        foreach ($listeners as $class => $properties) {
            if (! in_array(ListenerInterface::class, class_implements($class))) {
                continue;
            }

            if ($properties['connection'] != $this->connection) {
                continue;
            }

            if (count($properties['listen']) == 0) {
                continue;
            }

            $listenerManager->register($properties['listen'], new $class());
        }
    }

    /**
     * @return ListenerInterface[]
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

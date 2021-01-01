<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class ReplicationFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Replication[]
     */
    protected $replications = [];

    /**
     * @var PositionFactory
     */
    protected $positionFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->positionFactory = $container->get(PositionFactory::class);
    }

    /**
     * @throws RuntimeException
     * @return Replication
     */
    public function create(string $connection = 'default')
    {
        if (! isset($this->replications[$connection])) {
            $key = 'trigger.' . $connection;

            if (! $this->config->has($key)) {
                throw new RuntimeException('config ' . $key . ' is undefined.');
            }

            $config = $this->config->get($key);

            if ($binLogCurrent = $this->positionFactory->create($connection)->get()) {
                $config['binlog_filename'] = $binLogCurrent->getBinFileName();
                $config['binlog_position'] = $binLogCurrent->getBinLogPosition();
            }

            $this->replications[$connection] = new Replication($config);
        }

        return $this->replications[$connection];
    }
}

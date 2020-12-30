<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger\Subscriber;

use MySQLReplication\Definitions\ConstEventsNames;
use MySQLReplication\Event\DTO\HeartbeatDTO;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class HeartbeatSubscriber extends AbstractSubscriber
{
    const CACHE_KEY_PREFIX = 'trigger_heartbeat_latest_binlog:';

    const CACHE_TTL = 3600;

    /**
     * @var CacheInterface
     */
    protected $cache;

    public function __construct(ContainerInterface $container, string $connection = 'default')
    {
        parent::__construct($container, $connection);

        $this->cache = $container->get(CacheInterface::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConstEventsNames::HEARTBEAT => 'onHeartbeat',
        ];
    }

    public function onHeartbeat(HeartbeatDTO $event): void
    {
        $this->cache->set(self::CACHE_KEY_PREFIX . $this->connection, $event->getEventInfo()->getBinLogCurrent(), self::CACHE_TTL);
    }
}

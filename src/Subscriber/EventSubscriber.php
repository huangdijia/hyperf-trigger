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

use Huangdijia\Trigger\Constact\ListenerInterface;
use Huangdijia\Trigger\ListenerManager;
use Hyperf\Utils\Coroutine\Concurrent;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use MySQLReplication\Event\DTO\TableMapDTO;

class EventSubscriber extends AbstractEventSubscriber
{
    /**
     * @var ListenerManager
     */
    protected $listenerManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var null|Concurrent
     */
    protected $concurrent;

    public function __construct(ListenerManager $listenerManager, array $config = [])
    {
        $this->listenerManager = $listenerManager;
        $this->config = $config;

        $concurrentLimit = $config['concurrent']['limit'] ?? null;

        if ($concurrentLimit && is_numeric($concurrentLimit)) {
            $this->concurrent = new Concurrent((int) $concurrentLimit);
        }
    }

    protected function allEvents(EventDTO $event): void
    {
        $eventType = $event->getType();

        if (($event instanceof RowsDTO) || ($event instanceof TableMapDTO)) {
            $table = $event->getTableMap()->getTable();
            $eventType = sprintf('%s.%s', $table, $event);
        }

        foreach ($this->listenerManager->get($eventType) as $listeners) {
            foreach ($listeners as $evt => $class) {
                /** @var ListenerInterface $listener */
                $listener = new $class();
                $callback = function () use ($listener, $event) {
                    return $listener->process($event);
                };

                if ($this->concurrent) {
                    $this->concurrent->create($callback);
                } else {
                    parallel([$callback]);
                }
            }
        }
    }
}

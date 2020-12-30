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

use Huangdijia\Trigger\ListenerManager;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use MySQLReplication\Event\DTO\TableMapDTO;

class EventSubscriber extends AbstractEventSubscriber
{
    /**
     * @var ListenerManager
     */
    protected $listenerManager;

    public function __construct($listenerManager)
    {
        $this->listenerManager = $listenerManager;
    }

    protected function allEvents(EventDTO $event): void
    {
        $events = ['*'];

        if (($event instanceof RowsDTO) || ($event instanceof TableMapDTO)) {
            $events[] = sprintf('%s.*', $event->getTableMap()->getTable());
            $events[] = sprintf('%s.%s', $event->getTableMap()->getTable(), $event->getType());
        } else {
            $events[] = $event->getType();
        }

        foreach ($events as $key) {
            foreach ($this->listenerManager->get($key) as $listener) {
                co(function () use ($listener, $event) {
                    return $listener->process($event);
                });
            }
        }
    }
}

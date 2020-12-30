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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Coroutine\Concurrent;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use MySQLReplication\Event\DTO\TableMapDTO;
use MySQLReplication\Event\EventSubscribers;
use Psr\Log\LoggerInterface;

class EventSubscriber extends EventSubscribers
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

    /**
     * @var null|StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ListenerManager $listenerManager, array $config = [], ?LoggerInterface $logger = null)
    {
        $this->listenerManager = $listenerManager;
        $this->config = $config;
        $this->logger = $logger;

        $concurrentLimit = $config['concurrent']['limit'] ?? null;

        if ($concurrentLimit && is_numeric($concurrentLimit)) {
            $this->concurrent = new Concurrent((int) $concurrentLimit);
        }
    }

    protected function allEvents(EventDTO $event): void
    {
        // $this->logger && $this->logger->info($event->__toString());

        $eventType = $event->getType();

        if (($event instanceof RowsDTO) || ($event instanceof TableMapDTO)) {
            $table = $event->getTableMap()->getTable();
            $eventType = sprintf('%s.%s', $table, $eventType);
        }

        // $this->logger && $this->logger->info($eventType . ':' . json_encode($this->listenerManager->get($eventType)));

        $registered = $this->listenerManager->get($eventType);
        // $this->logger && $this->logger->info($eventType . ':' . json_encode($registered, JSON_PRETTY_PRINT));

        foreach ($registered as $listeners) {
            foreach ($listeners as $evt => $class) {
                /** @var ListenerInterface $listener */
                $listener = new $class();
                $callback = function () use ($listener, $event) {
                    return $listener->process($event);
                };

                $this->logger && $this->logger->info(sprintf('[trigger] %s[%s] triggered.', $class, $eventType));

                if ($this->concurrent) {
                    $this->concurrent->create($callback);
                } else {
                    parallel([$callback]);
                }
            }
        }
    }
}

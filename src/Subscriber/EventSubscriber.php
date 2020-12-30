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
use Huangdijia\Trigger\ListenerManagerFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Coroutine\Concurrent;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use MySQLReplication\Event\DTO\TableMapDTO;
use Psr\Container\ContainerInterface;

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

    public function __construct(ContainerInterface $container, string $connection = 'default')
    {
        parent::__construct($container, $connection);

        $this->listenerManager = $container->get(ListenerManagerFactory::class)->create($this->connection);
        $this->config = $container->get(ConfigInterface::class)->get('trigger.' . $this->connection);

        $concurrentLimit = $config['concurrent']['limit'] ?? null;

        if ($concurrentLimit && is_numeric($concurrentLimit)) {
            $this->concurrent = new Concurrent((int) $concurrentLimit);
        }
    }

    protected function allEvents(EventDTO $event): void
    {
        // $this->logger->info($event->__toString());

        $eventType = $event->getType();

        if (($event instanceof RowsDTO) || ($event instanceof TableMapDTO)) {
            $table = $event->getTableMap()->getTable();
            $eventType = sprintf('%s.%s', $table, $eventType);
        }

        // $this->logger->info($eventType . ':' . json_encode($this->listenerManager->get($eventType)));

        $registered = $this->listenerManager->get($eventType);
        // $this->logger->info($eventType . ':' . json_encode($registered, JSON_PRETTY_PRINT));

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

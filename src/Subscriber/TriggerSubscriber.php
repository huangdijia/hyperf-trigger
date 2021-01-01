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

use Huangdijia\Trigger\Annotation\Trigger;
use Huangdijia\Trigger\Constact\TriggerInterface;
use Huangdijia\Trigger\TriggerManager;
use Huangdijia\Trigger\TriggerManagerFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Coroutine\Concurrent;
use MySQLReplication\Definitions\ConstEventsNames;
use MySQLReplication\Event\DTO\DeleteRowsDTO;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use MySQLReplication\Event\DTO\UpdateRowsDTO;
use MySQLReplication\Event\DTO\WriteRowsDTO;
use Psr\Container\ContainerInterface;

class TriggerSubscriber extends AbstractSubscriber
{
    /**
     * @var TriggerManager
     */
    protected $triggerManager;

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

        $this->triggerManager = $container->get(TriggerManagerFactory::class)->create($this->connection);
        $this->config = $container->get(ConfigInterface::class)->get('trigger.' . $this->connection);

        $concurrentLimit = $config['concurrent']['limit'] ?? null;

        if ($concurrentLimit && is_numeric($concurrentLimit)) {
            $this->concurrent = new Concurrent((int) $concurrentLimit);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConstEventsNames::UPDATE => 'onUpdate',
            ConstEventsNames::DELETE => 'onDelete',
            ConstEventsNames::WRITE => 'onWrite',
        ];
    }

    protected function allEvents(EventDTO $event): void
    {
        if (! ($event instanceof RowsDTO)) {
            return;
        }

        $table = $event->getTableMap()->getTable();
        $eventType = $event->getType();
        $triggers = $this->triggerManager->get($table, $eventType);
        // $this->logger->info($eventType . ':' . json_encode($triggers, JSON_PRETTY_PRINT));

        foreach ($triggers as $class) {
            /** @var TriggerInterface $trigger */
            $trigger = new $class();

            switch ($eventType) {
                case ConstEventsNames::UPDATE:
                    /** @var UpdateRowsDTO $event */
                    foreach ($event->getValues() as $row) {
                        $callback = function () use ($trigger, $row) {
                            $trigger->onUpdate($row['before'], $row['after']);
                        };
                        if ($this->concurrent) {
                            $this->concurrent->create($callback);
                        } else {
                            parallel([$callback]);
                        }
                    }
                    break;
                case ConstEventsNames::DELETE:
                    /** @var DeleteRowsDTO $event */
                    foreach ($event->getValues() as $old) {
                        $callback = function () use ($trigger, $old) {
                            $trigger->onDelete($old);
                        };
                        if ($this->concurrent) {
                            $this->concurrent->create($callback);
                        } else {
                            parallel([$callback]);
                        }
                    }
                    break;
                case ConstEventsNames::WRITE:
                    /** @var WriteRowsDTO $event */
                    foreach ($event->getValues() as $new) {
                        $callback = function () use ($trigger, $new) {
                            $trigger->onWrite($new);
                        };
                        if ($this->concurrent) {
                            $this->concurrent->create($callback);
                        } else {
                            parallel([$callback]);
                        }
                    }
                    break;
            }

            // $this->logger && $this->logger->info(sprintf('[trigger] %s[%s] triggered.', $class, $eventType));
        }
    }
}

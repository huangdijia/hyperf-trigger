<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger\Listener;

use Huangdijia\Trigger\Annotation\Trigger;
use Huangdijia\Trigger\Constact\TriggerInterface;
use Huangdijia\Trigger\TriggerManagerFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Utils\ApplicationContext;

class RegisterTriggerListener implements ListenerInterface
{
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if (ApplicationContext::hasContainer()) {
            /** @var TriggerManagerFactory $factory */
            $factory = ApplicationContext::getContainer()->get(TriggerManagerFactory::class);
            $triggers = AnnotationCollector::getClassesByAnnotation(Trigger::class);
            $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);

            foreach ($triggers as $class => $property) {
                if (! in_array(TriggerInterface::class, class_implements($class))) {
                    continue;
                }

                if (count($property->events) == 0) {
                    continue;
                }

                $factory->get($property->replication ?: 'default')->register($property->table, $property->events, $class);

                $logger->info(sprintf('[trigger] %s [replication:%s events:%s] registered by %s listener.', $class, $property->replication, implode(',', $property->events), __CLASS__));
            }
        }
    }
}

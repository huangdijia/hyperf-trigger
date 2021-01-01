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

use Huangdijia\Trigger\Annotation\Subscriber;
use Huangdijia\Trigger\SubscriberManagerFactory;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Utils\ApplicationContext;

class RegisterSubsciberListener implements ListenerInterface
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
            /** @var SubscriberManagerFactory $factory */
            $factory = ApplicationContext::getContainer()->get(SubscriberManagerFactory::class);
            $subscribers = AnnotationCollector::getClassesByAnnotation(Subscriber::class);

            foreach ($subscribers as $class => $property) {
                $factory->create($property->connection ?: 'default')->register($property->connection ?? 'default', $class);
            }
        }
    }
}

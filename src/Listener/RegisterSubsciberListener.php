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
use Psr\Container\ContainerInterface;

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
            /** @var ContainerInterface $container */
            $container = ApplicationContext::getContainer();
            /** @var SubscriberManagerFactory $factory */
            $factory = $container->get(SubscriberManagerFactory::class);
            $subscribers = AnnotationCollector::getClassesByAnnotation(Subscriber::class);

            foreach ($subscribers as $class => $property) {
                $factory->get($property->replication ?: 'default')->register($class);
            }
        }
    }
}

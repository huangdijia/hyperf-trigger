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

use Huangdijia\Trigger\Listener\RegisterSubsciberListener;
use Huangdijia\Trigger\Listener\RegisterTriggerListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [
                PositionFactory::class => PositionFactory::class,
                ReplicationFactory::class => ReplicationFactory::class,
                SubscriberManagerFactory::class => SubscriberManagerFactory::class,
                TriggerManagerFactory::class => TriggerManagerFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [
                RegisterTriggerListener::class,
                RegisterSubsciberListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'config file.',
                    'source' => __DIR__ . '/../publish/trigger.php',
                    'destination' => BASE_PATH . '/config/autoload/trigger.php',
                ],
            ],
        ];
    }
}

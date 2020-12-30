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

use Hyperf\Utils\Str;

class ListenerManager
{
    /**
     * @var array
     */
    protected $listeners;

    /**
     * @param string|string[] $event
     * @param string $listener
     */
    public function register($event, $listener)
    {
        foreach ((array) $event as $e) {
            if (! isset($this->listeners[$e])) {
                $this->listeners[$e] = [];
            }

            $this->listeners[$e][] = $listener;
        }
    }

    /**
     * @return array[string[]]
     */
    public function get(string $eventType)
    {
        $listeners = [];

        foreach ((array) $this->listeners as $event => $listener) {
            /* @var array $listeners */
            if (Str::is($event, $eventType)) {
                if (! isset($listeners[$event])) {
                    $listeners[$event] = [];
                }

                $listeners[$event] = array_merge($listeners[$event], $listener);
            }
        }

        return $listeners;
    }
}

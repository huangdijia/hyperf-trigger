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

use Huangdijia\Trigger\Constact\ListenerInterface;
use Hyperf\Utils\Str;

class ListenerManager
{
    /**
     * @var array
     */
    protected $listeners;

    /**
     * @param string|string[] $event
     */
    public function register($event, ListenerInterface $listener)
    {
        foreach ((array) $event as $e) {
            $this->listeners[$e] = $listener;
        }
    }

    /**
     * @return ListenerInterface[]
     */
    public function get(string $pattern)
    {
        $listeners = [];

        foreach ($this->listeners ?? [] as $event => $listener) {
            if (Str::is($pattern, $event)) {
                $listeners[] = $listener;
            }
        }

        return $listeners;
    }

    public function deregister(string $event)
    {
        unset($this->listeners[$event]);
    }
}

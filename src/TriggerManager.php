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

use Hyperf\Utils\Arr;

class TriggerManager
{
    /**
     * @var array
     */
    protected $triggers;

    /**
     * @param string|string[] $events
     * @param string $trigger
     */
    public function register(string $table, $events, $trigger)
    {
        foreach ((array) $events as $event) {
            if (! isset($this->triggers[$table])) {
                $this->triggers[$table] = [];
            }

            if (! isset($this->triggers[$table][$event])) {
                $this->triggers[$table][$event] = [];
            }

            $this->triggers[$table][$event][] = $trigger;
        }
    }

    /**
     * @return string[]
     */
    public function get(string $table, string $event)
    {
        return Arr::get($this->triggers, $table . '.' . $event, []);
    }
}

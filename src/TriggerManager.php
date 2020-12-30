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

class TriggerManager
{
    /**
     * @var array
     */
    protected $triggers;

    /**
     * @param string|string[] $event
     * @param string $trigger
     */
    public function register($event, $trigger)
    {
        foreach ((array) $event as $e) {
            if (! isset($this->triggers[$e])) {
                $this->triggers[$e] = [];
            }

            $this->triggers[$e][] = $trigger;
        }
    }

    /**
     * @return array[string[]]
     */
    public function get(string $eventType)
    {
        $triggers = [];

        foreach ((array) $this->triggers as $event => $trigger) {
            if (Str::is($event, $eventType)) {
                if (! isset($triggers[$event])) {
                    $triggers[$event] = [];
                }

                $triggers[$event] = array_merge($triggers[$event], $trigger);
            }
        }

        return $triggers;
    }
}

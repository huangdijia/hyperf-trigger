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

class SubscriberManager
{
    /**
     * @var array
     */
    protected $subscribers;

    /**
     * @param string $connection
     * @param string $subscriber
     */
    public function register($connection, $subscriber)
    {
        if (! isset($this->subscribers[$connection])) {
            $this->subscribers[$connection] = [];
        }

        $this->subscribers[$connection][] = $subscriber;
    }

    /**
     * @return string[]
     */
    public function get(string $connection)
    {
        return $this->subscribers[$connection] ?? [];
    }
}

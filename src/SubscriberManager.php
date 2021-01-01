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
     * @var string[]
     */
    protected $subscribers;

    /**
     * @param string $subscriber
     */
    public function register($subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * @return string[]
     */
    public function get()
    {
        return $this->subscribers ?: [];
    }
}

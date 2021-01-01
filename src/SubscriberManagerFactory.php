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

use Huangdijia\Trigger\Constact\FactoryInterface;

class SubscriberManagerFactory implements FactoryInterface
{
    /**
     * @var SubscriberManager[]
     */
    protected $managers = [];

    /**
     * @return SubscriberManager
     */
    public function get(string $replication = 'default')
    {
        if (! isset($this->managers[$replication])) {
            $this->managers[$replication] = new SubscriberManager();
        }

        return $this->managers[$replication];
    }
}

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

class TriggerManagerFactory
{
    /**
     * @var TriggerManager[]
     */
    protected $managers = [];

    /**
     * @return TriggerManager
     */
    public function create(string $connection = 'default')
    {
        if (! isset($this->managers[$connection])) {
            $this->managers[$connection] = new TriggerManager();
        }

        return $this->managers[$connection];
    }
}

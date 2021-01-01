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
use Psr\Container\ContainerInterface;

class PositionFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $positions = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Position
     */
    public function get(string $replication = 'default')
    {
        if (! isset($this->positions[$replication])) {
            $this->positions[$replication] = new Position($this->container, $replication);
        }

        return $this->positions[$replication];
    }
}

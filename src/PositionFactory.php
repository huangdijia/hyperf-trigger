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

use Psr\Container\ContainerInterface;

class PositionFactory
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
    public function create(string $connection = 'default')
    {
        if (! isset($this->positions[$connection])) {
            $this->positions[$connection] = new Position($this->container, $connection);
        }

        return $this->positions[$connection];
    }
}

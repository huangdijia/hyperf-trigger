<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Subscriber extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $connection = 'default';

    public function __construct($value = null)
    {
        if (isset($value['connection']) && is_string($value['connection'])) {
            $this->connection = $value['connection'];
        }
    }
}

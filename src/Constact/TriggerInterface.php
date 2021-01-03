<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Huangdijia\Trigger\Constact;

interface TriggerInterface
{
    public function onWrite(array $new);

    public function onUpdate(array $old, array $new);

    public function onDelete(array $old);
}

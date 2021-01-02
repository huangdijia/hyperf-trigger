<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-trigger.
 *
 * @link     https://github.com/huangdijia/hyperf-trigger
 * @document https://github.com/huangdijia/hyperf-trigger/blob/main/README.md
 * @contact  hdj@addcn.com
 */
namespace Huangdijia\Trigger\Command;

use Huangdijia\Trigger\Annotation\Trigger;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class TriggersCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $name = 'trigger:triggers';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->addOption('replication', 'R', InputOption::VALUE_OPTIONAL, 'replication');
        $this->setDescription('List all triggers.');
    }

    public function handle()
    {
        $triggers = AnnotationCollector::getClassesByAnnotation(Trigger::class);
        var_dump($triggers);
    }
}

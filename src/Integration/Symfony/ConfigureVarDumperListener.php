<?php

declare(strict_types=1);

namespace Paysera\RoadRunnerBundle\Integration\Symfony;

use Paysera\RoadRunnerBundle\Event\WorkerStartEvent;
use Spiral\RoadRunner\Environment\Mode;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Reset the VarDumper handler to use the profiler
 * data collector dumper even in CLI mode.
 */
final class ConfigureVarDumperListener
{
    private DataDumperInterface $dumper;
    private ClonerInterface $cloner;
    private bool $rrEnabled;

    public function __construct(DataDumperInterface $dumper, ClonerInterface $cloner, ?string $rrMode = null)
    {
        $this->dumper = $dumper;
        $this->cloner = $cloner;
        $this->rrEnabled = $rrMode === Mode::MODE_HTTP;
    }

    public function __invoke(WorkerStartEvent $event): void
    {
        if ($this->rrEnabled) {
            VarDumper::setHandler(function ($var) {
                $data = $this->cloner->cloneVar($var);
                $this->dumper->dump($data);
            });
        }
    }
}

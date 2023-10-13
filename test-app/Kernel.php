<?php

namespace SampleApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // @phpstan-ignore-next-line
    private function getConfigDir(): string
    {
        return __DIR__.'/config';
    }
}

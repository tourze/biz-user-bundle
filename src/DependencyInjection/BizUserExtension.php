<?php

namespace BizUserBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class BizUserExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return dirname(__DIR__) . '/Resources/config';
    }
}

<?php

namespace Validation;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Glob;

class Module
{
    public function getConfig(): array
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }
}

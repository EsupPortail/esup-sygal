<?php

namespace Application\View\Helper;

use Interop\Container\ContainerInterface;

class LanguageSelectorHelperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new LanguageSelectorHelper($config['languages']['language-list']);
    }
}

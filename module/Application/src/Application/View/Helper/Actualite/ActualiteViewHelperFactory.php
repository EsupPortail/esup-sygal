<?php

namespace Application\View\Helper\Actualite;

use Interop\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ActualiteViewHelperFactory
{
    public function __invoke(ContainerInterface $container): ActualiteViewHelper
    {
        $config = $container->get('Config');

        $actualite = $config['actualite'];

        Assert::keyExists($actualite, 'actif');
        Assert::keyExists($actualite, 'flux');

        $helper = new ActualiteViewHelper();
        $helper->setEnabled($actualite['actif']);
        $helper->setUrl($actualite['flux']);

        return $helper;
    }
}
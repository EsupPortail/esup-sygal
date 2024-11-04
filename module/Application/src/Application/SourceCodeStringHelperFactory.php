<?php

namespace Application;

use Interop\Container\ContainerInterface;
use UnicaenApp\Exception\RuntimeException;

class SourceCodeStringHelperFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SourceCodeStringHelper
    {
        $config = $container->get('Config');
        $prefix = $this->getDefaultPrefixFromConfig($config);

        $helper = new SourceCodeStringHelper();
        $helper->setDefaultPrefix($prefix);

        return $helper;
    }

    /**
     * @param array $config
     * @return string
     */
    private function getDefaultPrefixFromConfig(array $config): string
    {
        $key = 'default_prefix_for_source_code';

        if (! isset($config['sygal'][$key])) {
            throw new RuntimeException(
                "Anomalie: le paramètre de config ['sygal'][$key] est introuvable.");
        }

        return $config['sygal'][$key];
    }
}

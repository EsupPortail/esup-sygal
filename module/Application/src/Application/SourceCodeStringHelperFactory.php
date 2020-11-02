<?php

namespace Application;

use UnicaenApp\Exception\RuntimeException;
use Interop\Container\ContainerInterface;

class SourceCodeStringHelperFactory
{
    /**
     * Create helper
     *
     * @param ContainerInterface $container
     * @return SourceCodeStringHelper
     */
    public function __invoke(ContainerInterface $container)
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
    private function getDefaultPrefixFromConfig(array $config)
    {
        $key = 'default_prefix_for_source_code';

        if (! isset($config['sygal'][$key])) {
            throw new RuntimeException(
                "Anomalie: le paramètre de config ['sygal'][$key] est introuvable.");
        }

        return $config['sygal'][$key];
    }
}

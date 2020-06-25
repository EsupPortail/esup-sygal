<?php

namespace Soutenance\Form\Configuration;

use Interop\Container\ContainerInterface;

class ConfigurationFormFactory
{

    /**
     * @param ContainerInterface $container
     * @return ConfigurationForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ConfigurationForm $form */
        $form = new ConfigurationForm();
        return $form;
    }
}
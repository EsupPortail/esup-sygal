<?php

namespace These\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TheseFormsManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TheseFormsManager
    {
        return new TheseFormsManager($container);
    }
}
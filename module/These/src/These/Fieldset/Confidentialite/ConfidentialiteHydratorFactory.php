<?php

namespace These\Fieldset\Confidentialite;

use Interop\Container\ContainerInterface;

class ConfidentialiteHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConfidentialiteHydrator
    {
        $hydrator = new ConfidentialiteHydrator();

        return $hydrator;
    }
}
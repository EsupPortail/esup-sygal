<?php

namespace These\Fieldset\Confidentialite;

use Interop\Container\ContainerInterface;
use These\Form\TheseSaisie\TheseSaisieHydrator;

class ConfidentialiteFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConfidentialiteFieldset
    {
        $fieldset = new ConfidentialiteFieldset('Confidentialite');

        /** @var ConfidentialiteHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ConfidentialiteHydrator::class);
        $fieldset->setHydrator($hydrator);

        return $fieldset;
    }
}
<?php

namespace Soutenance\Form\Confidentialite;

use Interop\Container\ContainerInterface;

class ConfidentialiteFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var ConfidentialiteHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ConfidentialiteHydrator::class);

        /** @var ConfidentialiteForm $form */
        $form = new ConfidentialiteForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}
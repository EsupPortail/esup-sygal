<?php

namespace Structure\Form\Factory;

use Interop\Container\ContainerInterface;
use Structure\Form\EtablissementForm;
use Structure\Form\Hydrator\EtablissementHydrator;

class EtablissementFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtablissementForm
    {
        /** @var EtablissementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('EtablissementHydrator');

        $form = new EtablissementForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}
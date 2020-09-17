<?php

namespace Soutenance\Form\QualiteEdition;

use Interop\Container\ContainerInterface;

class QualiteEditionFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var QualiteEditiontHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(QualiteEditiontHydrator::class);

        /** @var QualiteEditionForm $form */
        $form = new QualiteEditionForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}
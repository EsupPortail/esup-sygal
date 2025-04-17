<?php

namespace Formation\Form\Formation\FormationExterne;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormationExterneFormFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationExterneForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormationExterneForm
    {
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);

        $form = new FormationExterneForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}
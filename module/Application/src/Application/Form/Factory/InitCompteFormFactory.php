<?php

namespace Application\Form\Factory;

use Application\Form\InitCompteForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class InitCompteFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        /** @var InitCompteForm $form */
        $form = new InitCompteForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}

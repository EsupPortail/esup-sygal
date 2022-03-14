<?php

namespace Application\Form\Factory;

use Application\Form\InitCompteForm;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class InitCompteFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get('Doctrine\Laminas\Hydrator\DoctrineObject');

        /** @var InitCompteForm $form */
        $form = new InitCompteForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}

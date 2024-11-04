<?php

namespace Application\Form\Factory;

use Application\Form\AutorisationInscriptionForm;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Persistence\ObjectManager;
use Interop\Container\ContainerInterface;

class AutorisationInscriptionFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return AutorisationInscriptionForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ObjectManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineObject($em);

        $form = new AutorisationInscriptionForm('autorisationInscription');
        $form->setHydrator($hydrator);

        return $form;
    }
}
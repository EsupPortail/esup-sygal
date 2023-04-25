<?php

namespace Application\Form\Factory;

use Application\Form\Rapport\RapportAvisForm;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class RapportAvisFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return \Application\Form\Rapport\RapportAvisForm
     */
    public function __invoke(ContainerInterface $container): RapportAvisForm
    {
        /** @var ObjectManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineObject($em);

        $form = new RapportAvisForm('rapport-avis');
        $form->setHydrator($hydrator);

        return $form;
    }
}
<?php

namespace Application\Form\Factory;

use Application\Form\RapportMiparcoursForm;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class RapportMiparcoursFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RapportMiparcoursForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ObjectManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineObject($em);

        $form = new RapportMiparcoursForm('rapport');
        $form->setHydrator($hydrator);

        return $form;
    }
}
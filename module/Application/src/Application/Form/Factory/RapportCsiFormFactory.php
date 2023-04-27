<?php

namespace Application\Form\Factory;

use Application\Form\RapportCsiForm;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class RapportCsiFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RapportCsiForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ObjectManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineObject($em);

        $form = new RapportCsiForm('rapport');
        $form->setHydrator($hydrator);

        return $form;
    }
}
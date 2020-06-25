<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RapportAnnuelHydrator;
use Application\Form\RapportAnnuelForm;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class RapportAnnuelFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RapportAnnuelForm
     */
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();

        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new RapportAnnuelHydrator($em);

        $form = new RapportAnnuelForm('rapportAnnuel');
        $form->setHydrator($hydrator);

        return $form;
    }
}
<?php

namespace Soutenance\Form\SoutenanceMembre;

use Doctrine\ORM\EntityManager;
use Zend\Form\FormElementManager;


class SoutenanceMembreFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $servicelocator = $formElementManager->getServiceLocator();
        /** @var EntityManager $entityManager */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var SoutenanceMembreForm $form */
        $form = new SoutenanceMembreForm();
        $form->setEntityManager($entityManager);

        $hydrator = $servicelocator->get('HydratorManager')->get(SoutenanceMembreHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}
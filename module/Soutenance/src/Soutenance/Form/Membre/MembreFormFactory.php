<?php

namespace Soutenance\Form\Membre;

use Doctrine\ORM\EntityManager;
use Zend\Form\FormElementManager;


class MembreFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $servicelocator = $formElementManager->getServiceLocator();
        /** @var EntityManager $entityManager */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var MembreForm $form */
        $form = new MembreForm();
        $form->setEntityManager($entityManager);

        $hydrator = $servicelocator->get('HydratorManager')->get(MembreHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}
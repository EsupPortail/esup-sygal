<?php

namespace Soutenance\Form\Membre;

use Doctrine\ORM\EntityManager;
use Soutenance\Service\Qualite\QualiteService;
use Zend\Form\FormElementManager;


class MembreFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $servicelocator = $formElementManager->getServiceLocator();
        /**
         * @var EntityManager $entityManager
         * @var QualiteService $qualiteService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $qualiteService = $servicelocator->get(QualiteService::class);

        /** @var MembreForm $form */
        $form = new MembreForm();
        $form->setEntityManager($entityManager);
        $form->setQualiteService($qualiteService);
        $hydrator = $servicelocator->get('HydratorManager')->get(MembreHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}
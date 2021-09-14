<?php

namespace Formation\Controller;

use Application\Service\Etablissement\EtablissementService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Formation\FormationForm;
use Formation\Service\Formation\FormationService;
use Interop\Container\ContainerInterface;

class FormationControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var FormationService $formationService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $formationService = $container->get(FormationService::class);

        /**
         * @var FormationForm $formationForm
         */
        $formationForm = $container->get('FormElementManager')->get(FormationForm::class);

        $controller = new FormationController();
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setFormationService($formationService);
        $controller->setFormationForm($formationForm);
        return $controller;
    }
}
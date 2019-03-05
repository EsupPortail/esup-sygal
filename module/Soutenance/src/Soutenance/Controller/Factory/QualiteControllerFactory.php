<?php

namespace Soutenance\Controller\Factory;

use Soutenance\Controller\QualiteController;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Service\Membre\MembreService;
use Zend\Mvc\Controller\ControllerManager;

class QualiteControllerFactory
{
    /**
     * @param ControllerManager $manager
     * @return QualiteController
     */
    public function __invoke(ControllerManager $manager)
    {

        /**
         * @var MembreService $membreService
         */
        $membreService = $manager->getServiceLocator()->get(MembreService::class);

        /**
         * @var QualiteEditionForm $qualiteEditionForm
         */
        $qualiteEditionForm = $manager->getServiceLocator()->get('FormElementManager')->get(QualiteEditionForm::class);

        /** @var QualiteController $controller */
        $controller = new QualiteController();
        $controller->setMembreService($membreService);
        $controller->setQualiteEditionForm($qualiteEditionForm);

        return $controller;
    }
}
<?php

namespace Soutenance\Controller\Qualite;

use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Service\Qualite\QualiteService;
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
         * @var QualiteService $qualiteService
         */
        $qualiteService = $manager->getServiceLocator()->get(QualiteService::class);

        /**
         * @var QualiteEditionForm $qualiteEditionForm
         */
        $qualiteEditionForm = $manager->getServiceLocator()->get('FormElementManager')->get(QualiteEditionForm::class);

        /** @var QualiteController $controller */
        $controller = new QualiteController();
        $controller->setQualiteService($qualiteService);
        $controller->setQualiteEditionForm($qualiteEditionForm);

        return $controller;
    }
}
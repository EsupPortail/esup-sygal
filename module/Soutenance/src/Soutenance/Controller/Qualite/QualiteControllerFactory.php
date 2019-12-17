<?php

namespace Soutenance\Controller\Qualite;

use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireForm;
use Soutenance\Service\Qualite\QualiteService;
use Soutenance\Service\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireService;
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
         * @var QualiteLibelleSupplementaireService $qualiteLibelleSupplementaireService
         */
        $qualiteService = $manager->getServiceLocator()->get(QualiteService::class);
        $qualiteLibelleSupplementaireService = $manager->getServiceLocator()->get(QualiteLibelleSupplementaireService::class);

        /**
         * @var QualiteEditionForm $qualiteEditionForm
         * @var QualiteLibelleSupplementaireForm $qualiteLibelleSupplementaireForm
         */
        $qualiteEditionForm = $manager->getServiceLocator()->get('FormElementManager')->get(QualiteEditionForm::class);
        $qualiteLibelleSupplementaireForm = $manager->getServiceLocator()->get('FormElementManager')->get(QualiteLibelleSupplementaireForm::class);

        /** @var QualiteController $controller */
        $controller = new QualiteController();
        $controller->setQualiteService($qualiteService);
        $controller->setQualiteLibelleSupplementaireService($qualiteLibelleSupplementaireService);
        $controller->setQualiteEditionForm($qualiteEditionForm);
        $controller->setQualiteLibelleSupplementaireForm($qualiteLibelleSupplementaireForm);

        return $controller;
    }
}
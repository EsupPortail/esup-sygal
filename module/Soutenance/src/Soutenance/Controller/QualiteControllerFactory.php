<?php

namespace Soutenance\Controller;

use Interop\Container\ContainerInterface;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireForm;
use Soutenance\Service\Qualite\QualiteService;
use Soutenance\Service\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireService;
use Laminas\Mvc\Controller\ControllerManager;

class QualiteControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return QualiteController
     */
    public function __invoke(ContainerInterface $container)
    {

        /**
         * @var QualiteService $qualiteService
         * @var QualiteLibelleSupplementaireService $qualiteLibelleSupplementaireService
         */
        $qualiteService = $container->get(QualiteService::class);
        $qualiteLibelleSupplementaireService = $container->get(QualiteLibelleSupplementaireService::class);

        /**
         * @var QualiteEditionForm $qualiteEditionForm
         * @var QualiteLibelleSupplementaireForm $qualiteLibelleSupplementaireForm
         */
        $qualiteEditionForm = $container->get('FormElementManager')->get(QualiteEditionForm::class);
        $qualiteLibelleSupplementaireForm = $container->get('FormElementManager')->get(QualiteLibelleSupplementaireForm::class);

        /** @var QualiteController $controller */
        $controller = new QualiteController();
        $controller->setQualiteService($qualiteService);
        $controller->setQualiteLibelleSupplementaireService($qualiteLibelleSupplementaireService);
        $controller->setQualiteEditionForm($qualiteEditionForm);
        $controller->setQualiteLibelleSupplementaireForm($qualiteLibelleSupplementaireForm);

        return $controller;
    }
}
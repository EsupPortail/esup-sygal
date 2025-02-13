<?php

namespace HDR\Controller\Factory;

use Application\Service\Role\RoleService;
use Candidat\Service\CandidatService;
use HDR\Controller\HDRSaisieController;
use HDR\Form\Direction\DirectionForm;
use HDR\Form\Generalites\GeneralitesForm;
use HDR\Form\HDRSaisie\HDRSaisieForm;
use HDR\Form\Structures\StructuresForm;
use HDR\Service\HDRService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HDRSaisieControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return HDRSaisieController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): HDRSaisieController
    {
        /**
         * @var IndividuService $individuService
         * @var HDRService $hdrService
         * @var GeneralitesForm $generalitesForm
         * @var StructuresForm $structuresForm
         * @var DirectionForm $directionForm
         * @var HDRSaisieForm $hdrSaisieForm
         */
        $individuService = $container->get(IndividuService::class);
        $hdrService = $container->get(HDRService::class);
        $generalitesForm =  $container->get('FormElementManager')->get(GeneralitesForm::class);
        $structuresForm =  $container->get('FormElementManager')->get(StructuresForm::class);
        $directionForm =  $container->get('FormElementManager')->get(DirectionForm::class);
        $hdrSaisieForm = $container->get('FormElementManager')->get(HDRSaisieForm::class);

        $controller = new HDRSaisieController();
        $controller->setIndividuService($individuService);
        $controller->setHDRService($hdrService);
        $controller->setHDRSaisieForm($hdrSaisieForm);
        $controller->setGeneralitesForm($generalitesForm);
        $controller->setStructuresForm($structuresForm);
        $controller->setDirectionForm($directionForm);

        /** @var CandidatService $candidatService */
        $candidatService = $container->get(CandidatService::class);
        $controller->setCandidatService($candidatService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

        return $controller;
    }
}
<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Module\ModuleForm;
use Formation\Service\Formation\FormationService;
use Formation\Service\Module\ModuleService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ModuleControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ModuleController
    {
        /**
         * @var EntityManager $entityManager
         * @var FormationService $formationService
         * @var ModuleService $moduleService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $formationService = $container->get(FormationService::class);
        $moduleService = $container->get(ModuleService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);
        /**
         * @var ModuleForm $moduleForm
         */
        $moduleForm = $container->get('FormElementManager')->get(ModuleForm::class);

        $controller = new ModuleController();
        $controller->setEntityManager($entityManager);
        $controller->setFormationService($formationService);
        $controller->setModuleService($moduleService);
        $controller->setAnneeUnivService($anneeUnivService);
        $controller->setModuleForm($moduleForm);
        return $controller;
    }
}
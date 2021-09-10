<?php

namespace Formation\Controller;

use Application\Service\Etablissement\EtablissementService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Module\ModuleForm;
use Formation\Service\Module\ModuleService;
use Interop\Container\ContainerInterface;

class ModuleControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var ModuleService $moduleService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $moduleService = $container->get(ModuleService::class);

        /**
         * @var ModuleForm $moduleForm
         */
        $moduleForm = $container->get('FormElementManager')->get(ModuleForm::class);

        $controller = new ModuleController();
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setModuleService($moduleService);
        $controller->setModuleForm($moduleForm);
        return $controller;
    }
}
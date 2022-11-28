<?php

namespace Structure\Controller\Factory;

use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Controller\SubstitutionController;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class SubstitutionControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SubstitutionController
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get('EtablissementService');
        $ecoleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $structureService = $container->get('StructureService');

        $controller = new SubstitutionController();
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setStructureService($structureService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}
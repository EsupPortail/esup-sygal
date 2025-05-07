<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Application\Service\Rapport\RapportService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Service\RapportActiviteService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Information\Service\InformationService;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationServiceInterface;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return IndexController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndexController
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get('TheseService');

        $controller = new IndexController();
        $controller->setTheseService($theseService);

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get('EtablissementService');
        $controller->setEtablissementService($etablissementService);

        /**
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         */
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);

        /**
         * @var InformationService $informationService
         */
        $informationService = $container->get(InformationService::class);
        $controller->setInformationService($informationService);

        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $controller->setEntityManager($entityManager);

        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);
        $controller->setRapportService($rapportService);

        /**
         * @var RapportActiviteService $informationService
         */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $controller->setRapportActiviteService($rapportActiviteService);

        /** @var \RapportActivite\Rule\Creation\RapportActiviteCreationRule $rapportActiviteCreationRule */
        $rapportActiviteCreationRule = $container->get(RapportActiviteCreationRule::class);
        $controller->setRapportActiviteCreationRule($rapportActiviteCreationRule);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $controller->setDoctorantService($doctorantService);

        /**
         * @var VariableService $variableService
         */
        $variableService = $container->get('VariableService');
        $controller->setVariableService($variableService);

        /** @var AuthenticationServiceInterface $authenticationService */
        $authenticationService = $container->get('Laminas\\Authentication\\AuthenticationService');
        $controller->setAuthenticationService($authenticationService);

        return $controller;
    }
}

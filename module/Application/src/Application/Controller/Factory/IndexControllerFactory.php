<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

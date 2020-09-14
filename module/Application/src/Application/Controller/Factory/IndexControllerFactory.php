<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Application\Service\Actualite\ActualiteService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
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
         * @var VariableService $variableService
         */
        $variableService = $container->get('VariableService');
        $controller->setVariableService($variableService);

        /** @var AuthenticationServiceInterface $authenticationService */
        $authenticationService = $container->get('Zend\\Authentication\\AuthenticationService');
        $controller->setAuthenticationService($authenticationService);

        /** @var ActualiteService $actualiteService */
        $actualiteService = $container->get(ActualiteService::class);
        $controller->setActualiteService($actualiteService);
        
        return $controller;
    }
}

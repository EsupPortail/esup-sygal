<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url as UrlHelper;

/**
 * @author Unicaen
 */
class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected $notifierServiceClass = NotifierService::class;

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotifierService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotifierService $service */
        $service = parent::__invoke($serviceLocator);

        /**
         * @var VariableService         $variableService
         * @var EcoleDoctoraleService   $ecoleDoctoraleService
         * @var UniteRechercheService   $uniteRechercheService
         * @var IndividuService         $individuService
         * @var RoleService             $roleService
         */

        $variableService = $serviceLocator->get('VariableService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteRechercheService = $serviceLocator->get('UniteRechercheService');
        $individuService = $serviceLocator->get('IndividuService');
        $roleService = $serviceLocator->get('RoleService');

        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get('router');
        $viewHelperManager = $serviceLocator->get('ViewHelperManager');
        /* @var UrlHelper $urlHelper */
        $urlHelper = $viewHelperManager->get('Url');
        $urlHelper->setRouter($router);

        $service->setVariableService($variableService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setUrlHelper($urlHelper);
        $service->setIndividuService($individuService);
        $service->setRoleService($roleService);

        return $service;
    }
}

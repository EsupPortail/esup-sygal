<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\MailerService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\RendererInterface;

/**
 *
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotificationServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationService
     */

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var MailerService $mailerService
         * @var VariableService $variableService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var UniteRechercheService $uniteRechercheService
         */
        $mailerService = $serviceLocator->get('UnicaenApp\Service\Mailer');
        $variableService = $serviceLocator->get('VariableService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteRechercheService = $serviceLocator->get('UniteRechercheService');

        /* @var $router TreeRouteStack */
        $router = $serviceLocator->get('router');

        /* @var $renderer RendererInterface */
        $renderer = $serviceLocator->get('view_renderer');

        $options = $this->getOptions($serviceLocator);

        $service = new NotificationService($renderer);
        $service->setMailerService($mailerService);
        $service->setVariableService($variableService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setOptions($options);

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    private function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! isset($config['sygal']['notification'])) {
            return [];
        }

        return $config['sygal']['notification'];
    }
}

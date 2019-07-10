<?php

namespace Application\Service;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\Parametre\ParametreService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Service\Workflow\WorkflowService;
use Retraitement\Service\RetraitementService;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserContextServiceAwareInitializer implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param                         $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        if ($instance instanceof UserContextServiceAwareInterface) {
            $instance->setUserContextService($serviceLocator->get('UnicaenAuth\Service\UserContext'));
        }

        return $instance;
    }
}
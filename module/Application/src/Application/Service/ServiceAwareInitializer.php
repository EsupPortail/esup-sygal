<?php

namespace Application\Service;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotificationService;
use Application\Service\Parametre\ParametreService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\Variable\VariableService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Service\Workflow\WorkflowService;
use Retraitement\Service\RetraitementService;
use UnicaenLdap\Service\LdapPeopleServiceAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceAwareInitializer implements InitializerInterface
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

        $services = [
            //'UtilisateurService'     => UtilisateurService::class,
            'RoleService'            => RoleService::class,
            //'ParametreService'       => ParametreService::class,
            //'VariableService'        => VariableService::class,
            //'TheseService'           => TheseService::class,
            //'DoctorantService'       => DoctorantService::class,
            'FichierService'         => FichierService::class,
            'VersionFichierService'  => VersionFichierService::class,
            'ValiditeFichierService' => ValiditeFichierService::class,
            'WorkflowService'        => WorkflowService::class,
            'ValidationService'      => ValidationService::class,
            'UserContextService'     => UserContextService::class,
            'RetraitementService'    => RetraitementService::class,
            'EcoleDoctoraleService'  => EcoleDoctoraleService::class,
            'UniteRechercheService'  => UniteRechercheService::class,
            'IndividuService'        => IndividuService::class,
            'NotificationService'    => NotificationService::class,
            'EtablissementService'   => EtablissementService::class,
        ];

        foreach ($services as $name => $class) {
            $interface = $class . 'AwareInterface';
            if ($instance instanceof $interface) {
                $service = $serviceLocator->get($name);
                $method = 'set' . $name;
                $instance->{$method}($service);
            }
        }

        if ($instance instanceof LdapPeopleServiceAwareInterface) {
            $instance->setLdapPeopleService($serviceLocator->get('LdapServicePeople'));
        }

        return $instance;
    }
}
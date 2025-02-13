<?php

namespace Application\Service;

use Application\Service\Role\RoleService;
use Depot\Service\FichierThese\FichierTheseService;
use Depot\Service\Workflow\WorkflowService;
use Fichier\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Retraitement\Service\RetraitementService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\These\TheseService;

class ServiceAwareInitializer implements InitializerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $services = [
            'RoleService'            => RoleService::class,
            'TheseService'           => TheseService::class,
            'FichierTheseService'    => FichierTheseService::class,
            'VersionFichierService'  => VersionFichierService::class,
            'ValiditeFichierService' => ValiditeFichierService::class,
            'WorkflowService'        => WorkflowService::class,
            'UserContextService'     => UserContextService::class,
            'RetraitementService'    => RetraitementService::class,
            'EcoleDoctoraleService'  => EcoleDoctoraleService::class,
            'UniteRechercheService'  => UniteRechercheService::class,
            'IndividuService'        => IndividuService::class,
            'EtablissementService'   => EtablissementService::class,
        ];

        foreach ($services as $name => $class) {
            $interface = $class . 'AwareInterface';
            if ($instance instanceof $interface) {
                $service = $container->get($name);
                $method = 'set' . $name;
                $instance->{$method}($service);
            }
        }

        return $instance;
    }
}
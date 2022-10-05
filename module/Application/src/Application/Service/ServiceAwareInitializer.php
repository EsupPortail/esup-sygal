<?php

namespace Application\Service;

use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\FichierThese\FichierTheseService;
use Individu\Service\IndividuService;
use Application\Service\Role\RoleService;
use These\Service\These\TheseService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Validation\ValidationService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Application\Service\Workflow\WorkflowService;
use Interop\Container\ContainerInterface;
use Retraitement\Service\RetraitementService;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class ServiceAwareInitializer implements InitializerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $services = [
            //'UtilisateurService'     => UtilisateurService::class,
            'RoleService'            => RoleService::class,
            //'ParametreService'       => ParametreService::class,
            //'VariableService'        => VariableService::class,
            'TheseService'           => TheseService::class,
            //'DoctorantService'       => DoctorantService::class,
            'FichierTheseService'    => FichierTheseService::class,
            'VersionFichierService'  => VersionFichierService::class,
            'ValiditeFichierService' => ValiditeFichierService::class,
            'WorkflowService'        => WorkflowService::class,
            'ValidationService'      => ValidationService::class,
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
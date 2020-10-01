<?php

namespace Application\Service\These\Factory;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\AuthorizeService;
use Webmozart\Assert\Assert;

class TheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ValidationService   $validationService
         * @var NotifierService     $notifierService
         * @var FichierTheseService $fichierTheseService
         * @var VariableService     $variableService
         * @var UserContextService  $userContextService
         * @var AuthorizeService    $authorizeService
         */
        $validationService = $container->get('ValidationService');
        $notifierService = $container->get(NotifierService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $variableService = $container->get('VariableService');
        $userContextService = $container->get('UserContextService');
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);

        /** @var FileService $fileService */
        $fileService = $container->get(FileService::class);

        $service = new TheseService();
        $service->setValidationService($validationService);
        $service->setNotifierService($notifierService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setVariableService($variableService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setFileService($fileService);
        $service->setAuthorizeService($authorizeService);

        $this->injectConfig($service, $container);

        return $service;
    }

    private function injectConfig(TheseService $service, ContainerInterface $container)
    {
        $config = $container->get('Config');
        Assert::keyExists($config, 'sygal');
        Assert::keyExists($config['sygal'], 'depot_version_corrigee');

        $configDepotVersionCorrigee = $config['sygal']['depot_version_corrigee'];
        Assert::keyExists($configDepotVersionCorrigee, 'resaisir_autorisation_diffusion');
        Assert::keyExists($configDepotVersionCorrigee, 'resaisir_attestations');

        $service->setResaisirAutorisationDiffusionVersionCorrigee($configDepotVersionCorrigee['resaisir_autorisation_diffusion']);
        $service->setResaisirAttestationsVersionCorrigee($configDepotVersionCorrigee['resaisir_attestations']);
    }
}

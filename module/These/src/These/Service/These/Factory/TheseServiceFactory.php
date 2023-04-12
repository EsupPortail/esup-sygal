<?php

namespace These\Service\These\Factory;

use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class TheseServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseService
    {
        /**
         * @var ActeurService       $acteurService
         * @var MembreService       $membreService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        $service = new TheseService();
        $service->setActeurService($acteurService);
        $service->setMembreService($membreService);
        $service->setEtablissementService($etablissementService);
        $service->setFichierStorageService($fileService);

        return $service;
    }
}

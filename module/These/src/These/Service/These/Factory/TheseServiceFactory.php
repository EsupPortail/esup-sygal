<?php

namespace These\Service\These\Factory;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Source\SourceService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Structure\Service\Etablissement\EtablissementService;
use Acteur\Service\ActeurThese\ActeurTheseService;
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
         * @var ActeurTheseService       $acteurService
         * @var MembreService       $membreService
         */
        $acteurService = $container->get(ActeurTheseService::class);
        $membreService = $container->get(MembreService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        $service = new TheseService();
        $service->setActeurTheseService($acteurService);
        $service->setMembreService($membreService);
        $service->setAnneeUnivService($anneeUnivService);
        $service->setEtablissementService($etablissementService);
        $service->setFichierStorageService($fileService);
        $service->setSourceService($sourceService);

        return $service;
    }
}

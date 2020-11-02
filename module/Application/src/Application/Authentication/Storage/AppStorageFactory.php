<?php

namespace Application\Authentication\Storage;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;

class AppStorageFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @param ContainerInterface $container
     * @return AppStorage
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $container->get('UtilisateurService');

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);

        $etablissementService = $this->locateEtablissementService($container);

        $service = new AppStorage();
        $service->setUtilisateurService($utilisateurService);
        $service->setDoctorantService($doctorantService);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}
<?php

namespace Application\Authentication\Storage;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Utilisateur\UtilisateurService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AppStorageFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @param ServiceLocatorInterface $sl
     * @return AppStorage
     */
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $sl->get('UtilisateurService');

        /** @var DoctorantService $doctorantService */
        $doctorantService = $sl->get(DoctorantService::class);

        $etablissementService = $this->locateEtablissementService($sl);

        $service = new AppStorage();
        $service->setUtilisateurService($utilisateurService);
        $service->setDoctorantService($doctorantService);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}
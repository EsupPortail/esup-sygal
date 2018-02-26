<?php

namespace Application\Authentication\Storage;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AppStorageFactory
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return AppStorage
     */
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var DoctorantService $doctorantService */
        $doctorantService = $sl->get(DoctorantService::class);

        /** @var EcoleDoctoraleService $edService */
        $edService = $sl->get(EcoleDoctoraleService::class);

        /** @var UniteRechercheService $urService */
        $urService = $sl->get(UniteRechercheService::class);

        $service = new AppStorage();
        $service->setDoctorantService($doctorantService);
        $service->setEcoleDoctoraleService($edService);
        $service->setUniteRechercheService($urService);

        return $service;
    }
}
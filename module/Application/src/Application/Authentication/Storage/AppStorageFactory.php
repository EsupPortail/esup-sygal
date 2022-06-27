<?php

namespace Application\Authentication\Storage;

use Application\Entity\UserWrapperFactory;
use Doctorant\Service\DoctorantService;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;

class AppStorageFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @param ContainerInterface $container
     * @return AppStorage
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AppStorage
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $container->get('UtilisateurService');

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);

        $etablissementService = $this->locateEtablissementService($container);

        /** @var UserWrapperFactory $userWrapperFactory */
        $userWrapperFactory = $container->get(UserWrapperFactory::class);

        $service = new AppStorage();
        $service->setUtilisateurService($utilisateurService);
        $service->setDoctorantService($doctorantService);
        $service->setEtablissementService($etablissementService);
        $service->setUserWrapperFactory($userWrapperFactory);

        return $service;
    }
}
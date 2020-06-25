<?php

namespace Application\Service\Individu;

use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class IndividuServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $container->get('UtilisateurService');

        $service = new IndividuService();
        $service->setUtilisateurService($utilisateurService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
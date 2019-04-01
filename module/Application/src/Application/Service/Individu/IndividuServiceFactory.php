<?php

namespace Application\Service\Individu;

use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndividuServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $sl->get('UtilisateurService');

        $service = new IndividuService();
        $service->setUtilisateurService($utilisateurService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
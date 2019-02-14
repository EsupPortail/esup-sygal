<?php

namespace Application\Service\Utilisateur;

use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class UtilisateurServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return UtilisateurService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $service = new UtilisateurService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}

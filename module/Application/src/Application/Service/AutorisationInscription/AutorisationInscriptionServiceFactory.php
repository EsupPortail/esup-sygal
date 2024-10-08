<?php

namespace Application\Service\AutorisationInscription;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\RapportValidation\RapportValidationService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AutorisationInscriptionServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AutorisationInscriptionService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var RapportValidationService $rapportValidationService
         */
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $service = new AutorisationInscriptionService();
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}
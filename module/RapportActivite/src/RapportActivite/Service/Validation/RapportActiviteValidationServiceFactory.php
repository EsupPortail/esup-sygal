<?php

namespace RapportActivite\Service\Validation;

use Application\Entity\Db\TypeValidation;
use Application\Service\Individu\IndividuService;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Interop\Container\ContainerInterface;

class RapportActiviteValidationServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationService
    {
        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);

        /** @var \Application\Service\Validation\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        $service = new RapportActiviteValidationService();
        $service->setIndividuService($individuService);
        $service->setUserContextService($userContextService);
        $service->setTypeValidation($typeValidation);

        return $service;
    }
}
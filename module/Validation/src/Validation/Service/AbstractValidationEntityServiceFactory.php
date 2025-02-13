<?php

namespace Validation\Service;

use Individu\Service\IndividuService;
use Psr\Container\ContainerInterface;
use UnicaenAuthentification\Service\UserContext;

class AbstractValidationEntityServiceFactory
{
    protected string $validationEntityServiceClass;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AbstractValidationEntityService
    {
        $service = new $this->validationEntityServiceClass;

        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /** @var \Application\Service\UserContextService $userContext */
        $userContext = $container->get(UserContext::class);
        $service->setUserContextService($userContext);

        /** @var \Validation\Service\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        return $service;
    }
}
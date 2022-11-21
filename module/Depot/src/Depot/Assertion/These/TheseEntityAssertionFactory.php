<?php

namespace Depot\Assertion\These;

use Application\Service\Validation\ValidationService;
use Depot\Service\These\DepotService;
use Depot\Service\Validation\DepotValidationService;
use Psr\Container\ContainerInterface;
use Depot\Service\FichierThese\FichierTheseService;
use These\Service\These\TheseService;

class TheseEntityAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseEntityAssertion
    {
        /** @var  $assertion */
        $assertion = new TheseEntityAssertion();

        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $assertion->setUserContextService($userContext);

        /** @var \These\Service\These\TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $assertion->setTheseService($theseService);

        /** @var \Application\Service\Validation\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $assertion->setValidationService($validationService);

        /** @var \Depot\Service\These\DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $assertion->setDepotService($depotService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $assertion->setDepotValidationService($depotValidationService);

        /** @var \Depot\Service\FichierThese\FichierTheseService $fichierTheseService */
        $fichierTheseService = $container->get(FichierTheseService::class);
        $assertion->setFichierTheseService($fichierTheseService);

        return $assertion;
    }
}
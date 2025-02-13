<?php

namespace Soutenance\Assertion\These;

use Application\Service\UserContextService;
use Validation\Service\ValidationThese\ValidationTheseService;
use Interop\Container\ContainerInterface;

class PropositionTheseAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return PropositionTheseAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var UserContextService $userContext
         * @var ValidationTheseService $validationService
         */
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $validationService = $container->get(ValidationTheseService::class);

        /** @var  $assertion */
        $assertion = new PropositionTheseAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setValidationTheseService($validationService);

        return $assertion;

    }
}
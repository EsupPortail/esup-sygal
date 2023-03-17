<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Interop\Container\ContainerInterface;

class PropositionAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return PropositionAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var UserContextService $userContext
         * @var ValidationService $validationService
         */
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $validationService = $container->get('ValidationService');

        /** @var  $assertion */
        $assertion = new PropositionAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setValidationService($validationService);

        return $assertion;

    }
}
<?php

namespace Soutenance\Assertion\HDR;

use Application\Service\UserContextService;
use Validation\Service\ValidationHDR\ValidationHDRService;
use Interop\Container\ContainerInterface;
use UnicaenAuthentification\Service\UserContext;

class PropositionHDRAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return PropositionHDRAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var UserContextService $userContext
         * @var ValidationHDRService $validationService
         */
        $userContext = $container->get(UserContext::class);
        $validationService = $container->get(ValidationHDRService::class);

        /** @var  $assertion */
        $assertion = new PropositionHDRAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setValidationHDRService($validationService);

        return $assertion;

    }
}
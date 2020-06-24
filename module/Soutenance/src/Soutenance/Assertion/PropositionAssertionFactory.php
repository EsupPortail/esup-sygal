<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class PropositionAssertionFactory {

    public function __invoke(ServiceLocatorInterface $container)
    {
        /**
         * @var UserContextService $userContext
         * @var ValidationService $validationService
         */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $validationService = $container->get('ValidationService');

        /** @var  $assertion */
        $assertion = new PropositionAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setValidationService($validationService);

        return $assertion;

    }
}
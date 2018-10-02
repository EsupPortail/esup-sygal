<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class PropositionAssertionFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var UserContextService $userContext
         * @var ValidationService $validationService
         */
        $userContext = $serviceLocator->get('UnicaenAuth\Service\UserContext');
        $validationService = $serviceLocator->get('ValidationService');

        /** @var  $assertion */
        $assertion = new PropositionAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setValidationService($validationService);

        return $assertion;

    }
}
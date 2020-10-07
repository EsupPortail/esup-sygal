<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;

class PresoutenanceAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return PresoutenanceAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var UserContextService $userContext
         */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');

        /** @var  $assertion */
        $assertion = new PresoutenanceAssertion();
        $assertion->setUserContextService($userContext);

        return $assertion;

    }
}
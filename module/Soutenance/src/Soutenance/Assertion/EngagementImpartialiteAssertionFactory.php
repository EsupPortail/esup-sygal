<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;

class EngagementImpartialiteAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var UserContextService $userContext
         */
        $userContext = $container->get('UnicaenAuth\Service\UserContext');

        /** @var  $assertion */
        $assertion = new EngagementImpartialiteAssertion();
        $assertion->setUserContextService($userContext);

        return $assertion;

    }
}
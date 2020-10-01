<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionService;

class AvisSoutenanceAssertionFactory {

    /**
     * @param ContainerInterface $container
     * @return AvisSoutenanceAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var PropositionService $propositionService
         * @var UserContextService $userContext
         */
        $propositionService = $container->get(PropositionService::class);
        $userContext = $container->get('UnicaenAuth\Service\UserContext');

        /** @var  $assertion */
        $assertion = new AvisSoutenanceAssertion();
        $assertion->setPropositionService($propositionService);
        $assertion->setUserContextService($userContext);

        return $assertion;

    }
}
<?php

namespace Soutenance\Assertion;

use Application\Service\UserContextService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AvisSoutenanceAssertionFactory {

    public function __invoke(ServiceLocatorInterface $container)
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
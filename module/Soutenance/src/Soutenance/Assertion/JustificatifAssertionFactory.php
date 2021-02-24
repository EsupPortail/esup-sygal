<?php

namespace Soutenance\Assertion;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;

class JustificatifAssertionFactory
{

    /**
     * @param ContainerInterface $container
     * @return JustificatifAssertion
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         * @var UserContextService $userContext
         */
        $parametreService = $container->get(ParametreService::class);
        $propositionService = $container->get(PropositionService::class);
        $theseService = $container->get(TheseService::class);
        $userContext = $container->get('UnicaenAuth\Service\UserContext');

        /** @var  $assertion */
        $assertion = new JustificatifAssertion();
        $assertion->setParametreService($parametreService);
        $assertion->setPropositionService($propositionService);
        $assertion->setTheseService($theseService);
        $assertion->setUserContextService($userContext);

        return $assertion;

    }
}
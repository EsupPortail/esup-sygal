<?php

namespace Soutenance\Assertion;

use HDR\Service\HDRService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionService;
use UnicaenAuthentification\Service\UserContext;
use UnicaenParametre\Service\Parametre\ParametreService;

class JustificatifAssertionFactory
{

    /**
     * @param ContainerInterface $container
     * @return JustificatifAssertion
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : JustificatifAssertion
    {
        /**
         * @var TheseService $theseService
         * @var HDRService $hdrService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         * @var UserContextService $userContext
         */
        $parametreService = $container->get(ParametreService::class);
        $propositionService = $container->get(PropositionService::class);
        $theseService = $container->get(TheseService::class);
        $hdrService = $container->get(HDRService::class);
        $userContext = $container->get(UserContext::class);

        /** @var  $assertion */
        $assertion = new JustificatifAssertion();
        $assertion->setParametreService($parametreService);
        $assertion->setPropositionService($propositionService);
        $assertion->setTheseService($theseService);
        $assertion->setHDRService($hdrService);
        $assertion->setUserContextService($userContext);

        return $assertion;

    }
}
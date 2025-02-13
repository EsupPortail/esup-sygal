<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use HDR\Service\HDRService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionService;
use UnicaenAuthentification\Service\UserContext;
use UnicaenParametre\Service\Parametre\ParametreService;

class InterventionAssertionFactory
{

    /**
     * @param ContainerInterface $container
     * @return InterventionAssertion
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : InterventionAssertion
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
        $hdrService = $container->get(HDRService::class);
        $userContext = $container->get(UserContext::class);

        /** @var  $assertion */
        $assertion = new InterventionAssertion();
        $assertion->setParametreService($parametreService);
        $assertion->setPropositionService($propositionService);
        $assertion->setTheseService($theseService);
        $assertion->setHDRService($hdrService);
        $assertion->setUserContextService($userContext);

        $this->injectCommons($assertion, $container);

        return $assertion;

    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, \Psr\Container\ContainerInterface $container)
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}
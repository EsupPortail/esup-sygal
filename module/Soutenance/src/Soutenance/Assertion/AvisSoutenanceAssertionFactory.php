<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
use UnicaenAuthentification\Service\UserContext;

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
        $userContext = $container->get(UserContext::class);
        $membreService = $container->get(MembreService::class);
        $avisService = $container->get(AvisService::class);


        /** @var  $assertion */
        $assertion = new AvisSoutenanceAssertion();
        $assertion->setPropositionService($propositionService);
        $assertion->setUserContextService($userContext);
        $assertion->setAvisService($avisService);
        $assertion->setMembreService($membreService);

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
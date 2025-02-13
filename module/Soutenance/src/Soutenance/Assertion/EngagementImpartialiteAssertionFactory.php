<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextService;
use HDR\Service\HDRService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\These\TheseService;

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
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $theseService = $container->get(TheseService::class);
        $hdrService = $container->get(HDRService::class);

        /** @var  $assertion */
        $assertion = new EngagementImpartialiteAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setTheseService($theseService);
        $assertion->setHDRService($hdrService);

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
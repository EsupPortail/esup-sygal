<?php

namespace HDR\Assertion;

use Application\Assertion\AbstractAssertion;
use HDR\Service\HDRService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenAuthentification\Service\UserContext;

class HDRAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): HDRAssertion
    {
        /** @var  $assertion */
        $assertion = new HDRAssertion();

        $userContext = $container->get(UserContext::class);
        $assertion->setUserContextService($userContext);

        /** @var HDRService $hdrService */
        $hdrService = $container->get(HDRService::class);
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
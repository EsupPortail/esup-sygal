<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Service\UserContextService;
use HDR\Service\HDRService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\UserContext;

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
        $userContext = $container->get(UserContext::class);
        $theseService = $container->get(TheseService::class);
        $hdrService = $container->get(HDRService::class);
        $messageCollector = $container->get('MessageCollector');


        /** @var  $assertion */
        $assertion = new PresoutenanceAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setTheseService($theseService);
        $assertion->setHDRService($hdrService);
        $assertion->setServiceMessageCollector($messageCollector);
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
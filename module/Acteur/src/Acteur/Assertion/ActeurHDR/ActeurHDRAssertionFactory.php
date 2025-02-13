<?php

namespace Acteur\Assertion\ActeurHDR;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Assertion\AbstractAssertion;
use HDR\Service\HDRService;
use Psr\Container\ContainerInterface;
use UnicaenAuthentification\Service\UserContext;

class ActeurHDRAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurHDRAssertion
    {
        /** @var  $assertion */
        $assertion = new ActeurHDRAssertion();

        /** @var \HDR\Service\HDRService $HDRService */
        $HDRService = $container->get(HDRService::class);
        $assertion->setHDRService($HDRService);

        /** @var ActeurHDRService $acteurService */
        $acteurService = $container->get(ActeurHDRService::class);
        $assertion->setActeurHDRService($acteurService);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container): void
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();
        $userContext = $container->get(UserContext::class);
        $messageCollector = $container->get('MessageCollector');

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
        $assertion->setUserContextService($userContext);
        $assertion->setServiceMessageCollector($messageCollector);
    }
}
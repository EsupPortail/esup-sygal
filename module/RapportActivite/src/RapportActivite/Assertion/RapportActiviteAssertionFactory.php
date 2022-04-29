<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use RapportActivite\Assertion\RapportActiviteAssertion;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAssertion
    {
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setRapportActiviteService($rapportActiviteService);
        $assertion->setServiceMessageCollector($messageCollector);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container)
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}
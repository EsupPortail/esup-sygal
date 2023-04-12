<?php

namespace RapportActivite\Assertion\Recherche;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;

class RapportActiviteRechercheAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteRechercheAssertion
    {
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new RapportActiviteRechercheAssertion();
        $assertion->setUserContextService($userContext);
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
<?php

namespace Doctorant\Assertion\These;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;

class TheseAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseAssertion
    {
        $assertion = new TheseAssertion();

        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $assertion->setUserContextService($userContext);

        $messageCollector = $container->get('MessageCollector');
        $assertion->setServiceMessageCollector($messageCollector);

        /** @var TheseEntityAssertion $theseEntityAssertion */
        $theseEntityAssertion = $container->get(TheseEntityAssertion::class);
        $assertion->setTheseEntityAssertion($theseEntityAssertion);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container)
    {
        /** @var \UnicaenPrivilege\Service\AuthorizeService $authorizeService */
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}
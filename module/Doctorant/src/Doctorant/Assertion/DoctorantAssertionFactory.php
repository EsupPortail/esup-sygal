<?php

namespace Doctorant\Assertion;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\UserContext;

class DoctorantAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantAssertion
    {
        $assertion = new DoctorantAssertion();

        $userContext = $container->get(UserContext::class);
        $assertion->setUserContextService($userContext);

        $messageCollector = $container->get('MessageCollector');
        $assertion->setServiceMessageCollector($messageCollector);

        $theseService = $container->get(TheseService::class);
        $assertion->setTheseService($theseService);

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

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}
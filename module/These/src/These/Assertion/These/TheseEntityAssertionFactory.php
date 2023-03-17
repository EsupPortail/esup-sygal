<?php

namespace These\Assertion\These;

use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;

class TheseEntityAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseEntityAssertion
    {
        /** @var  $assertion */
        $assertion = new TheseEntityAssertion();

        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $assertion->setUserContextService($userContext);

        /** @var \These\Service\These\TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $assertion->setTheseService($theseService);

        return $assertion;
    }
}
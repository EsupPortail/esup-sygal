<?php

namespace Acteur\Assertion\ActeurThese;

use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\UserContext;

class ActeurTheseAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurTheseAssertion
    {
        /** @var  $assertion */
        $assertion = new ActeurTheseAssertion();

        /** @var \These\Service\These\TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $assertion->setTheseService($theseService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $assertion->setActeurTheseService($acteurService);

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
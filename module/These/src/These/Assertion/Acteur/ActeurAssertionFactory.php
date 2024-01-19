<?php

namespace These\Assertion\Acteur;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class ActeurAssertionFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurAssertion
    {
        /** @var  $assertion */
        $assertion = new ActeurAssertion();

        /** @var \These\Service\These\TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $assertion->setTheseService($theseService);

        /** @var ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $assertion->setActeurService($acteurService);

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
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $messageCollector = $container->get('MessageCollector');

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
        $assertion->setUserContextService($userContext);
        $assertion->setServiceMessageCollector($messageCollector);
    }
}
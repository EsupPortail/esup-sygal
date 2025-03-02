<?php

namespace These\Assertion\Acteur;

use Application\Assertion\AbstractAssertion;
use Psr\Container\ContainerInterface;
use These\Service\Acteur\ActeurService;
use These\Service\CoEncadrant\CoEncadrantService;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\UserContext;

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

        /** @var CoEncadrantService $coEncadrantService */
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $assertion->setCoEncadrantService($coEncadrantService);

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
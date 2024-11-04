<?php

namespace Application\Assertion\AutorisationInscription;

use Application\Assertion\AbstractAssertion;
use Application\Service\AutorisationInscription\AutorisationInscriptionService;
use Application\Service\Rapport\RapportService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AutorisationInscriptionAssertionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AutorisationInscriptionAssertion
    {
        $userContext = $container->get('UnicaenAuth\Service\UserContext');
        $autorisationInscriptionService = $container->get(AutorisationInscriptionService::class);
        $rapportService = $container->get(RapportService::class);
        $messageCollector = $container->get('MessageCollector');

        /** @var  $assertion */
        $assertion = new AutorisationInscriptionAssertion();
        $assertion->setUserContextService($userContext);
        $assertion->setAutorisationInscriptionService($autorisationInscriptionService);
        $assertion->setRapportService($rapportService);
        $assertion->setServiceMessageCollector($messageCollector);

        $this->injectCommons($assertion, $container);

        return $assertion;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function injectCommons(AbstractAssertion $assertion, ContainerInterface $container)
    {
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $mvcEvent = $container->get('Application')->getMvcEvent();

        $assertion->setServiceAuthorize($authorizeService);
        $assertion->setMvcEvent($mvcEvent);
    }
}
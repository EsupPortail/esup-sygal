<?php

namespace Formation\Assertion\Justificatif;

use Doctorant\Service\DoctorantService;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Justificatif\JustificatifService;
use Formation\Service\Session\SessionService;
use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class JustificatifAssertionFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): JustificatifAssertion
    {
        /**
         * @see DoctorantService $doctorantService
         * @see InscriptionService $inscriptionService
         * @see SessionService $sessionService
         * @see JustificatifService $justificatifService
         */
        $doctorantService = $container->get(DoctorantService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $sessionService = $container->get(SessionService::class);
        $justificatifService = $container->get(JustificatifService::class);

        $assertion = new JustificatifAssertion();
        $assertion->setDoctorantService($doctorantService);
        $assertion->setInscriptionService($inscriptionService);
        $assertion->setSessionService($sessionService);
        $assertion->setJustificatifService($justificatifService);

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);
        return $assertion;
    }
}
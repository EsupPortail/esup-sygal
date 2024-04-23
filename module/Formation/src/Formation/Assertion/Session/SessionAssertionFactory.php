<?php

namespace Formation\Assertion\Session;

use Doctorant\Service\DoctorantService;
use Formation\Service\Inscription\InscriptionService;
use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class SessionAssertionFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SessionAssertion
    {
        /**
         * @see DoctorantService $doctorantService
         * @see InscriptionService $inscriptionService
         * @see ParametreService $parametreService
         */
        $doctorantService = $container->get(DoctorantService::class);

        $assertion = new SessionAssertion();
        $assertion->setDoctorantService($doctorantService);

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);
        return $assertion;
    }
}
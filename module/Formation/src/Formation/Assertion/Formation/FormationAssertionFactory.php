<?php

namespace Formation\Assertion\Formation;

use Doctorant\Service\DoctorantService;
use Formation\Service\Inscription\InscriptionService;
use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class FormationAssertionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationAssertion
    {
        /**
         * @see DoctorantService $doctorantService
         * @see InscriptionService $inscriptionService
         * @see ParametreService $parametreService
         */
        $doctorantService = $container->get(DoctorantService::class);

        $assertion = new FormationAssertion();
        $assertion->setDoctorantService($doctorantService);

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);

        return $assertion;
    }
}
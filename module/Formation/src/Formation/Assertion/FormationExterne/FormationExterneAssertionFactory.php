<?php

namespace Formation\Assertion\FormationExterne;

use Doctorant\Service\DoctorantService;
use Formation\Service\Formation\FormationExterne\FormationExterneService;
use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormationExterneAssertionFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationExterneAssertion
    {
        /**
         * @see DoctorantService $doctorantService
         * @see FormationExterneService $formationExterneService
         */
        $doctorantService = $container->get(DoctorantService::class);
        $formationExterneService = $container->get(FormationExterneService::class);

        $assertion = new FormationExterneAssertion();
        $assertion->setDoctorantService($doctorantService);
        $assertion->setFormationExterneService($formationExterneService);

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);
        return $assertion;
    }
}
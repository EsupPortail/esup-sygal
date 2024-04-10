<?php

namespace Formation\Assertion;

use Doctorant\Service\DoctorantService;
use Formation\Provider\Parametre\FormationParametres;
use Formation\Service\Inscription\InscriptionService;
use Laminas\Mvc\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class InscriptionAssertionFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAssertion
    {
        /**
         * @see DoctorantService $doctorantService
         * @see InscriptionService $inscriptionService
         * @see ParametreService $parametreService
         */
        $doctorantService = $container->get(DoctorantService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $parametreService = $container->get(ParametreService::class);

        $assertion = new InscriptionAssertion();
        $assertion->setDoctorantService($doctorantService);
        $assertion->setInscriptionService($inscriptionService);
        $assertion->setDelaiDescinscription($parametreService->getValeurForParametre(FormationParametres::CATEGORIE, FormationParametres::DELAI_ANNULATION_INSCRIPTION));

        /* @var $application Application */
        $application = $container->get('Application');
        $mvcEvent = $application->getMvcEvent();
        $assertion->setMvcEvent($mvcEvent);
        return $assertion;
    }
}
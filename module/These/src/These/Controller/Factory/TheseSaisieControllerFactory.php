<?php

namespace These\Controller\Factory;

use Application\Service\DomaineHal\DomaineHalService;
use Application\Service\Source\SourceService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Service\Etablissement\EtablissementService;
use These\Controller\TheseSaisieController;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class TheseSaisieControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return TheseSaisieController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseSaisieController
    {
        /**
         * @var ActeurService $acteurService
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var QualiteService $qualiteService
         * @var SourceService $sourceService
         * @var TheseService $theseService
         * @var TheseSaisieForm $theseSaisieForm
         */
        $acteurService = $container->get(ActeurService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $qualiteService = $container->get(QualiteService::class);
        $sourceService = $container->get(SourceService::class);
        $theseService = $container->get(TheseService::class);
        $domaineHalService = $container->get(DomaineHalService::class);
        $theseSaisieForm = $container->get('FormElementManager')->get(TheseSaisieForm::class);

        $controller = new TheseSaisieController();
        $controller->setActeurService($acteurService);
        $controller->setEtablissementService($etablissementService);
        $controller->setIndividuService($individuService);
        $controller->setQualiteService($qualiteService);
        $controller->setTheseService($theseService);
        $controller->setDomaineHalService($domaineHalService);
        $controller->setTheseSaisieForm($theseSaisieForm);

        $controller->setSource($sourceService->fetchApplicationSource());
        return $controller;
    }
}
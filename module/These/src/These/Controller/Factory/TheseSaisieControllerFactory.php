<?php

namespace These\Controller\Factory;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use These\Controller\TheseSaisieController;
use These\Form\TheseSaisie\TheseSaisieForm;
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
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var SourceService $sourceService
         * @var TheseService $theseService
         * @var TheseSaisieForm $theseSaisieForm
         */
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $theseService = $container->get(TheseService::class);
        $theseSaisieForm = $container->get('FormElementManager')->get(TheseSaisieForm::class);
        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        $controller = new TheseSaisieController();
        $controller->setEtablissementService($etablissementService);
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setTheseSaisieForm($theseSaisieForm);
        $controller->setSourceService($sourceService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $controller->setDoctorantService($doctorantService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setRoleService($roleService);

        $controller->setSource($sourceService->fetchApplicationSource());
        return $controller;
    }
}
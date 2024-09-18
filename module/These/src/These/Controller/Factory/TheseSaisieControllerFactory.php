<?php

namespace These\Controller\Factory;

use Application\Service\Role\RoleService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Controller\TheseSaisieController;
use These\Form\Financement\FinancementsForm;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Structures\StructuresForm;
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
         * @var IndividuService $individuService
         * @var TheseService $theseService
         * @var TheseSaisieForm $theseSaisieForm
         */
        $individuService = $container->get(IndividuService::class);
        $theseService = $container->get(TheseService::class);
        $generalitesForm =  $container->get('FormElementManager')->get(GeneralitesForm::class);
        $structuresForm =  $container->get('FormElementManager')->get(StructuresForm::class);
        $financementsForm =  $container->get('FormElementManager')->get(FinancementsForm::class);
        $theseSaisieForm = $container->get('FormElementManager')->get(TheseSaisieForm::class);

        $controller = new TheseSaisieController();
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setTheseSaisieForm($theseSaisieForm);
        $controller->setGeneralitesForm($generalitesForm);
        $controller->setStructuresForm($structuresForm);
        $controller->setFinancementsForm($financementsForm);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $controller->setDoctorantService($doctorantService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setRoleService($roleService);

        return $controller;
    }
}
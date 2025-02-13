<?php

namespace These\Controller\Factory;

use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Service\Role\RoleService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Controller\CoEncadrantController;
use These\Form\CoEncadrant\RechercherCoEncadrantForm;
use These\Service\CoEncadrant\CoEncadrantService;
use These\Service\Exporter\CoEncadrements\CoEncadrementsExporter;
use These\Service\These\TheseService;
use UnicaenRenderer\Service\Rendu\RenduService;

class CoEncadrantControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return CoEncadrantController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): CoEncadrantController
    {
        /**
         * @var ActeurTheseService $acteurService
         * @var CoEncadrantService $coEncadrantService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var FichierStorageService $fileService
         * @var RenduService $renduService
         * @var TheseService $theseService
         * @var UniteRechercheService $uniteRechercheService
 */
        $acteurService = $container->get(ActeurTheseService::class);
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $fileService = $container->get(FichierStorageService::class);
        $renduService = $container->get(RenduService::class);
        $theseService = $container->get('TheseService');
        $uniteRechercheService = $container->get(UniteRechercheService::class);
        $coEncadrementsExporter = $container->get(CoEncadrementsExporter::class);

        /**
         * @var RechercherCoEncadrantForm $rechercheCoEncadrantForm
         */
        $rechercheCoEncadrantForm = $container->get('FormElementManager')->get(RechercherCoEncadrantForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new CoEncadrantController();
        $controller->setActeurTheseService($acteurService);
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setIndividuService($individuService);
        $controller->setFichierStorageService($fileService);
        $controller->setRenduService($renduService);
        $controller->setTheseService($theseService);
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setRechercherCoEncadrantForm($rechercheCoEncadrantForm);
        $controller->setRenderer($renderer);
        $controller->setCoEncadrementsExporter($coEncadrementsExporter);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

        return $controller;
    }
}
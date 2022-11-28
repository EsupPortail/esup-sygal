<?php

namespace These\Form\TheseSaisie;

use Application\Service\Discipline\DisciplineService;
use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class TheseSaisieFormFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : TheseSaisieForm
    {
        /**
         * @var DisciplineService $disciplineService
         * @var EcoleDoctorale $ecoleDoctoraleService
         * @var EtablissementService $etablissementService
         * @var QualiteService $qualiteService
         * @var UniteRechercheService $uniteRechercheService
         */
        $disciplineService = $container->get(DisciplineService::class);
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $qualiteService = $container->get(QualiteService::class);
        $uniteRechercheService = $container->get(UniteRechercheService::class);

        /** @var TheseSaisieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(TheseSaisieHydrator::class);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');

        $form = new TheseSaisieForm();
        $form->setDisciplineService($disciplineService);
        $form->setEcoleDoctoraleService($ecoleDoctoraleService);
        $form->setEtablissementService($etablissementService);
        $form->setQualiteService($qualiteService);
        $form->setUniteRechercheService($uniteRechercheService);

        $form->setHydrator($hydrator);
        $form->setUrlDoctorant($renderer->url('recherche-doctorant', [], [], true));
        //todo route de recherche des directeurs
        $form->setUrlDirecteur($renderer->url('individu/rechercher', [], [], true));
        return $form;
    }
}
<?php

namespace Admission\Form\Fieldset\Inscription;

use Admission\Entity\Db\Inscription;
use Admission\Hydrator\Inscription\InscriptionHydrator;
use Admission\Service\Admission\AdmissionService;
use Application\Service\Discipline\DisciplineService;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Application\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;

class InscriptionFieldsetFactory
{
    use DisciplineServiceAwareTrait;
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionFieldset
    {
        /** @var InscriptionHydrator $inscriptionHydrator */
        $inscriptionHydrator = $container->get('HydratorManager')->get(InscriptionHydrator::class);

        $fieldset = new InscriptionFieldset();
        $fieldset->setHydrator($inscriptionHydrator);
        $fieldset->setObject(new Inscription());

        $admissionService = $container->get(AdmissionService::class);
        $fieldset->setAdmissionService($admissionService);

        $disciplineService = $container->get(DisciplineService::class);
        $disciplines = $disciplineService->getDisciplinesAsOptions('libelle','ASC','id');
        $fieldset->setSpecialites($disciplines);

        $structureService = $container->get(StructureService::class);
        $composantes = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_COMPOSANTE_ENSEIGNEMENT, 'structure.libelle', false);
        $fieldset->setComposantesEnseignement($composantes);

        $ecoles = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
        $fieldset->setEcolesDoctorales($ecoles);

        $unites = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle', false);
        $fieldset->setUnitesRecherche($unites);

        $etablissementService = $container->get(EtablissementService::class);
        $etablissementsInscription = $etablissementService->getRepository()->findAllEtablissementsInscriptions();
        $fieldset->setEtablissementsInscription($etablissementsInscription);

        $qualiteService = $container->get(QualiteService::class);
        $qualites = $qualiteService->getQualitesForAdmission();
        $fieldset->setQualites($qualites);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        /** @see AdmissionController::rechercherIndividuAction() */
        $fieldset->setUrlIndividuThese($renderer->url('admission/rechercher-individu', [], ["query" => []], true));

        /** @see PaysController::rechercherPaysAction() */
        $fieldset->setUrlPaysCoTutelle($renderer->url('pays/rechercher-pays', [], ["query" => []], true));

        return $fieldset;
    }
}
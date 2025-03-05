<?php

namespace Soutenance\Controller\These\Presoutenance;

use Acteur\Entity\Db\ActeurThese;
use Acteur\Fieldset\ActeurThese\ActeurTheseFieldset;
use Acteur\Form\ActeurThese\ActeurTheseFormAwareTrait;
use Application\Entity\Db\Role;
use Application\Entity\Db\Source;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Entity\Membre;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\AvisSoutenance\AvisSoutenancePdfExporter;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Exporter\ProcesVerbal\ProcesVerbalSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportSoutenance\RapportSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportTechnique\RapportTechniquePdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseService;
use UnicaenApp\Exception\RuntimeException;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;

/** @method FlashMessenger flashMessenger() */
/**
 * @property PropositionTheseService $propositionService
 * @property These $entity
 * @property PropositionThese $proposition
 * @property TheseService $entityService
 */
class PresoutenanceTheseController extends PresoutenanceController
{
    use ParametreServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

    use ActeurTheseFormAwareTrait;

    /** @var PhpRenderer */
    private PhpRenderer $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function presoutenanceAction() : ViewModel
    {
        $this->initializeFromType();

        /** @var Membre[] $membres */
        $membres = $this->proposition->getMembres()->toArray();
        $acteursMembres = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($membres);

        $rapporteurs = $this->propositionService->getRapporteurs($this->proposition);
        $acteursRapporteurs = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($rapporteurs);
        $nbRapporteurs = count($rapporteurs);

        $renduRapport = $this->proposition->getRenduRapport();
        if (!$renduRapport) $this->propositionService->initialisationDateRetour($this->proposition);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->justificatifService->generateListeJustificatif($this->proposition);
        $justificatifsOk = $this->justificatifService->isJustificatifsOk($this->proposition, $justificatifs);

        $documentsLiesSoutenance = $this->justificatifService->generateListeDocumentsLiesSoutenance($this->proposition);

        /** ==> clef: Membre->getActeur()->getIndividu()->getId() <== */
        $engagements = $this->engagementImpartialiteService->getEngagmentsImpartialiteByEntity($this->entity, $rapporteurs);
        $avis = $this->avisService->getAvisByProposition($this->proposition);

        $validationBDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $validationPDC = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $this->entity);

        /** Parametres ---------------------------------------------------------------------------------------------- */
        try {
            $deadline = $this->parametreService->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de la valeur d'un paramètre", 0 , $e);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/presoutenance-these/presoutenance');
        $vm->setVariables([
            'these' => $this->entity,
            'typeProposition' => $this->type,
            'proposition' => $this->proposition,
            'membres' => $membres,
            'acteursMembres' => $acteursMembres,
            'rapporteurs' => $rapporteurs,
            'acteursRapporteurs' => $acteursRapporteurs,
            'acteursPouvantEtrePresidentJury' => $this->acteurService->getRepository()->findAllActeursPouvantEtrePresidentDuJury($this->proposition),
            'engagements' => $engagements,
            'adresse' => $this->proposition->getAdresseActive(),
            'avis' => $avis,
            'tousLesEngagements' => $nbRapporteurs > 0 && count($engagements) === $nbRapporteurs,
            'tousLesAvis' => $nbRapporteurs > 0 && count($avis) === $nbRapporteurs,
            'urlFichierThese' => $this->urlFichierThese(),
            'validationBDD' => $validationBDD,
            'validationPDC' => $validationPDC,
            'justificatifsOk' => $justificatifsOk,
            'justificatifs' => $justificatifs,

            'deadline' => $deadline,

            'documentsLiesSoutenance' => $documentsLiesSoutenance,
        ]);
        return $vm;
    }

    /**
     * Ici on affecte au membre des individus enregistrés dans SyGAL
     */
    public function associerJurySygalAction() : ViewModel|Response
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $codeRole = "";
        switch ($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
            case Membre::RAPPORTEUR_ABSENT :
                $codeRole = Role::CODE_RAPPORTEUR_JURY;
                break;
            case Membre::MEMBRE_JURY :
                $codeRole = Role::CODE_MEMBRE_JURY;
                break;
        }
        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete($codeRole, $this->entity->getEtablissement());

        $individu = $this->params()->fromQuery('individu') ?  $this->individuService->getRepository()->find($this->params()->fromQuery('individu')) : new Individu();
        $acteur = $this->acteurService->newActeurThese($this->entity, $individu, $role);
        $acteur->setQualite($membre->getQualite());
        $acteur->setThese($this->entity);
        $acteur->setMembre($membre);

        $form = $this->acteurTheseForm;
        $form->bind($acteur);
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['these' => $this->entity->getId(), 'membre' => $membre->getId()], [], true));

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var ActeurTheseFieldset $acteurFieldset */
        $acteurFieldset = $form->get('acteur');
        $acteurFieldset->setRoles($roles);

        $viewModel = new ViewModel([
            'title' => "Association de " . $membre->getDenomination() . " à un individu " . $this->appInfos()->getNom(),
            'form' => $form,
            'membre' => $membre,
            'these' => $this->entity,
            'returnUrl' => $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['these' => $this->entity->getId(), 'membre' => $membre->getId()], ["query" => ["modal" => "1"]], true)
        ]);
        $viewModel->setTemplate('acteur/acteur/modifier');

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var ActeurThese $acteur **/
        $acteur = $form->getData();
        try {
            $this->acteurService->save($acteur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

//        //mise à jour du membre de soutenance
//        $membre->setActeur($acteur);
//        $this->getMembreService()->update($membre);
        $acteur->setMembre($membre);
        $this->acteurService->save($acteur);

        //creation de l'utilisateur
        if ($membre->estRapporteur()) {
            $this->createUtilisateurRapporteur($acteur, $membre);

            //quand c'est une thèse saisie dans SyGAL, on enregistre également un acteur avec le rôle Membre (ce qui était automatique quand cela provenait d'un SI)
            //cet acteur sera supprimé en même temps que l'acteur comportant le rôle Rapporteur, si une dissociation est faite
            if($acteur->getRole()->getCode() !== Role::CODE_RAPPORTEUR_ABSENT){
                $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_MEMBRE_JURY, $this->entity->getEtablissement());
                $acteurRoleMembre = $this->acteurService->newActeurThese($this->entity, $acteur->getIndividu(), $role);
                $acteurRoleMembre->setQualite($acteur->getQualite());
                $acteurRoleMembre->setEtablissement($acteur->getEtablissement());
                $acteurRoleMembre->setUniteRecherche($acteur->getUniteRecherche());
                $this->acteurService->save($acteurRoleMembre);
            }
        }

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['these' => $this->entity->getId()], [], true);
    }

    /** Document pour la signature en présidence */
    #[NoReturn] public function procesVerbalSoutenanceAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new ProcesVerbalSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
        ]);
        $exporter->export($this->entity->getId() . '_proces_verbal.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Procès verbal");
        exit;
    }

    #[NoReturn] public function avisSoutenanceAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new AvisSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
        ]);
        $exporter->export($this->entity->getId() . '_avis_soutenance.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Avis de soutenance");
        exit;
    }

    #[NoReturn] public function rapportSoutenanceAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new RapportSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
        ]);
        $exporter->export($this->entity->getId() . '_rapport_soutenance.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Rapport de soutenance");
        exit;
    }

    #[NoReturn] public function rapportTechniqueAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new RapportTechniquePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'these' => $this->entity,
            'informations' => $pdcData,
        ]);
        $exporter->export($this->entity->getId() . '_rapport_technique.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Rapport technique");
        exit;
    }

    /** Document pour la signature en présidence */
    #[NoReturn] public function convocationsAction(): void
    {
        $this->initializeFromType();
        $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->export($this->entity->getId() . '_convocation.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Convocations");
        exit;
    }

    private function findSignatureEcoleDoctorale(These $object): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $object->getEcoleDoctorale()->getStructure(),
            NatureFichier::CODE_SIGNATURE_CONVOCATION,
            $object->getEtablissement());

        if ($fichier === null) {
            return null;
        }

        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            return $this->fichierStorageService->getFileForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la signature de l'ED !", 0, $e);
        }
    }

    #[NoReturn] public function convocationDoctorantAction(): void
    {
        $this->initializeFromType();
        $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->exportDoctorant($this->entity->getId() . '_convocation.pdf');
        exit;
    }

    #[NoReturn] public function convocationMembreAction(): void
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
            'membre' => $membre,
        ]);
        $exporter->exportMembre($membre, $this->entity->getId() . '_convocation.pdf');
        exit;
    }

    /** SIMULATION DE JURY ********************************************************************************************/

    public function genererSimulationAction() : Response
    {
        $this->initializeFromType();
        $membres = $this->proposition->getMembres();

        /** @var Role $rapporteur */
        /** @var Role $membreJury */
        $rapporteur = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete('R', $this->entity->getEtablissement());
        $membreJury = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete('M', $this->entity->getEtablissement());

        /** @var Source $sygal */
        $sygal = $this->sourceService->getRepository()->findOneBy(['code' => 'SYGAL::sygal']);

        /** @var Membre $membre */
        foreach($membres as $membre) {
            /** @var Individu $individu */
            $source_code_individu = 'SyGAL_Simulation_' . $membre->getId();
            $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $source_code_individu]);
            if ($individu === null) {
                $individu = new Individu();
                $individu->setPrenom($membre->getPrenom());
                $individu->setNomUsuel($membre->getNom());
                $individu->setNomPatronymique($membre->getNom());
                $individu->setEmailPro($membre->getEmail());
                $individu->setSource($sygal);
                $individu->setSourceCode($source_code_individu);
                try {
                    $this->individuService->getEntityManager()->persist($individu);
                    $this->individuService->getEntityManager()->flush($individu);
                }catch (ORMException $e) {
                    throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
                }
            }

            if ($membre->estRapporteur()) {
                /** @var ActeurThese $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new ActeurThese();
                    $acteur->setRole($rapporteur);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($this->entity);
                    $acteur->setSource($sygal);
                    $acteur->setSourceCode($source_code_acteur);
                    try {
                        $this->acteurService->getEntityManager()->persist($acteur);
                        $this->acteurService->getEntityManager()->flush($acteur);
                    }catch (ORMException $e) {
                        throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
                    }
                }
            }

            if ($membre->estMembre()) {
                /** @var ActeurThese $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Membre_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new ActeurThese();
                    $acteur->setRole($membreJury);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($this->entity);
                    $acteur->setSource($sygal);
                    $acteur->setSourceCode($source_code_acteur);
                    try {
                        $this->acteurService->getEntityManager()->persist($acteur);
                        $this->acteurService->getEntityManager()->flush($acteur);
                    }catch (ORMException $e) {
                        throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
                    }
                }
            }
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function nettoyerSimulationAction() : Response
    {
        $this->initializeFromType();
        $membres = $this->proposition->getMembres();

        try {
            foreach ($membres as $membre) {
                /** @var ActeurThese $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);
                if ($acteur !== null) {
                    $this->acteurService->getEntityManager()->remove($acteur);
                    $this->acteurService->getEntityManager()->flush($acteur);
                }

                /** @var ActeurThese $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Membre_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);
                if ($acteur !== null) {
                    $this->acteurService->getEntityManager()->remove($acteur);
                    $this->acteurService->getEntityManager()->flush($acteur);
                }

                /** @var Individu $source_code_individu */
                $source_code_individu = 'SyGAL_Simulation_' . $membre->getId();
                $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $source_code_individu]);
                if ($individu !== null) {
                    $this->acteurService->getEntityManager()->remove($individu);
                    $this->acteurService->getEntityManager()->flush($individu);
                }
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en Base de donnée", 0 , $e);
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }
}
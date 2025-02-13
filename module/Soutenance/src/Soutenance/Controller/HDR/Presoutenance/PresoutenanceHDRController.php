<?php

namespace Soutenance\Controller\HDR\Presoutenance;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Fieldset\ActeurHDR\ActeurHDRFieldset;
use Acteur\Form\ActeurHDR\ActeurHDRFormAwareTrait;
use Application\Entity\Db\Role;
use Exception;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Entity\Membre;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\AvisSoutenance\AvisSoutenancePdfExporter;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Exporter\ProcesVerbal\ProcesVerbalSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportSoutenance\RapportSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportTechnique\RapportTechniquePdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use UnicaenApp\Exception\RuntimeException;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;

/** @method FlashMessenger flashMessenger() */
/**
 * @property PropositionHDRService $propositionService
 * @property HDR $entity
 * @property PropositionHDR $proposition
 */
class PresoutenanceHDRController extends PresoutenanceController
{
    use ParametreServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use FichierServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

    use ActeurHDRFormAwareTrait;

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

        $validationBDD = $this->validationService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);

        /** Parametres ---------------------------------------------------------------------------------------------- */
        try {
            $deadline = $this->parametreService->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de la valeur d'un paramètre", 0 , $e);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/presoutenance-hdr/presoutenance');
        $vm->setVariables([
            'hdr' => $this->entity,
            'typeProposition' => $this->type,
            'proposition' => $this->proposition,
            'rapporteurs' => $rapporteurs,
            'acteursRapporteurs' => $acteursRapporteurs,
            'membres' => $membres,
            'acteursMembres' => $acteursMembres,
            'engagements' => $engagements,
            'adresse' => $this->proposition->getAdresseActive(),
            'avis' => $avis,
            'tousLesEngagements' => $nbRapporteurs > 0 && count($engagements) === $nbRapporteurs,
            'tousLesAvis' => $nbRapporteurs > 0 && count($avis) === $nbRapporteurs,
            'urlFichierHDR' => $this->urlFichierHDR(),
            'validationBDD' => $validationBDD,
            'justificatifsOk' => $justificatifsOk,
            'justificatifs' => $justificatifs,

            'deadline' => $deadline,

            'documentsLiesSoutenance' => $documentsLiesSoutenance,
        ]);
        return $vm;
    }

    public function deassocierJuryAction() : Response
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

        $acteurs = $this->acteurService->getRepository()->findActeurByHDR($this->entity);
        $a = null;
        foreach ($acteurs as $acteur_) {
//            if ($acteur_ === $membre->getActeur()) $acteur = $acteur_;
            if ($acteur_ === $acteur) $a = $acteur_;
        }
        if (!$a) throw new RuntimeException("Aucun acteur à deassocier !");

//        //retrait dans membre de soutenance
//        $membre->setActeur(null);
//        $this->getMembreService()->update($membre);
        $acteur->setMembre($membre);
        $this->acteurService->save($acteur);
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        if(!$this->entity->getSource()->getImportable()) $this->acteurService->delete($acteur);

        $validation = $this->validationHDRService->getRepository()->findValidationByHDRAndCodeAndIndividu($this->entity, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu());
        if ($validation !== null) {
            $this->validationHDRService->unsignEngagementImpartialite($validation);
        }

        $utilisateur = $this->getMembreService()->getUtilisateur($membre);
        if ($utilisateur){
            try {
                $this->utilisateurService->supprimerUtilisateur($utilisateur);
            }catch (Exception $e) {
                throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
            }
        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['hdr' => $this->entity->getId()], [], true);
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
        $acteur = $this->acteurService->newActeurHDR($this->entity, $individu, $role);
        $acteur->setQualite($membre->getQualite());
        $acteur->setHDR($this->entity);

        $form = $this->acteurHDRForm;
        $form->bind($acteur);
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['hdr' => $this->entity->getId(), 'membre' => $membre->getId()], [], true));

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var ActeurHDRFieldset $acteurFieldset */
        $acteurFieldset = $form->get('acteur');
        $acteurFieldset->setRoles($roles);

        $viewModel = new ViewModel([
            'title' => "Association de " . $membre->getDenomination() . " à un individu " . $this->appInfos()->getNom(),
            'form' => $form,
            'membre' => $membre,
            'hdr' => $this->entity,
            'returnUrl' => $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/associer-jury-sygal", ['hdr' => $this->entity->getId(), 'membre' => $membre->getId()], ["query" => ["modal" => "1"]], true)
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

        /** @var ActeurHDR $acteur **/
        $acteur = $form->getData();
        try {
            //mise à jour du membre de soutenance
//        $membre->setActeur($acteur);
//        $this->getMembreService()->update($membre);
            $acteur->setMembre($membre);
            $this->acteurService->save($acteur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        //creation de l'utilisateur
        if ($membre->estRapporteur()) {
            $this->createUtilisateurRapporteur($acteur, $membre);
        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['hdr' => $this->entity->getId()], [], true);
    }

    /** Document pour la signature en présidence */
    #[NoReturn] public function procesVerbalSoutenanceAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new ProcesVerbalSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'hdr' => $this->entity,
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
            'hdr' => $this->entity,
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
            'hdr' => $this->entity,
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
            'hdr' => $this->entity,
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

        $validationMDD = $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'hdr' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->export($this->entity->getId() . '_convocation.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Convocations");
        exit;
    }

    #[NoReturn] public function convocationCandidatAction(): void
    {
        $this->initializeFromType();
        $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'hdr' => $this->entity,
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

        $validationMDD = $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'hdr' => $this->entity,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
            'membre' => $membre,
        ]);
        $exporter->exportMembre($membre, $this->entity->getId() . '_convocation.pdf');
        exit;
    }
}
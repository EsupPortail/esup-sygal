<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Role;
use Application\Entity\Db\Source;
use Application\Entity\Db\TypeValidation;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateInterval;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceFormAwareTrait;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormAwareTrait;
use Soutenance\Provider\Parametre\SoutenanceParametres;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\AvisSoutenance\AvisSoutenancePdfExporter;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Exporter\ProcesVerbal\ProcesVerbalSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportSoutenance\RapportSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportTechnique\RapportTechniquePdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Fieldset\Acteur\ActeurFieldset;
use These\Form\Acteur\ActeurFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuthentification\Service\Traits\UserServiceAwareTrait;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

/** @method FlashMessenger flashMessenger() */

class PresoutenanceController extends AbstractController
{
    use HorodatageServiceAwareTrait;
    use TheseServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use PropositionServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use AvisServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use UserServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use FichierServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use TokenServiceAwareTrait;
    use SourceServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use ActeurFormAwareTrait;

    use DateRenduRapportFormAwareTrait;
    use AdresseSoutenanceFormAwareTrait;

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
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $rapporteurs = $this->propositionService->getRapporteurs($proposition);
        $nbRapporteurs = count($rapporteurs);

        $renduRapport = $proposition->getRenduRapport();
        if (!$renduRapport) $this->propositionService->initialisationDateRetour($proposition);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->justificatifService->generateListeJustificatif($proposition);
        $justificatifsOk = $this->justificatifService->isJustificatifsOk($proposition, $justificatifs);

        $documentsLiesSoutenance = $this->justificatifService->generateListeDocumentsLiesSoutenance($proposition);

        /** ==> clef: Membre->getActeur()->getIndividu()->getId() <== */
        $engagements = $this->engagementImpartialiteService->getEngagmentsImpartialiteByThese($these, $rapporteurs);
        $avis = $this->avisService->getAvisByThese($these);

        $validationBDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $validationPDC = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);

        /** Parametres ---------------------------------------------------------------------------------------------- */
        try {
            $deadline = $this->parametreService->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de la valeur d'un paramètre", 0 , $e);
        }

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'rapporteurs' => $rapporteurs,
            'engagements' => $engagements,
            'adresse' => $proposition->getAdresseActive(),
            'avis' => $avis,
            'tousLesEngagements' => count($engagements) === $nbRapporteurs,
            'tousLesAvis' => count($avis) === $nbRapporteurs,
            'urlFichierThese' => $this->urlFichierThese(),
            'validationBDD' => $validationBDD,
            'validationPDC' => $validationPDC,
            'justificatifsOk' => $justificatifsOk,
            'justificatifs' => $justificatifs,

            'deadline' => $deadline,

            'documentsLiesSoutenance' => $documentsLiesSoutenance,
        ]);
    }

    public function dateRenduRapportAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $form = $this->getDateRenduRapportForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/date-rendu-rapport', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->propositionService->update($proposition);
                $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Date de rendu");
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form' => $form,
            'title' => 'Modification de la date de rendu des rapports',
        ]);
        return $vm;
    }

    /**
     * Ici on affecte au membre des acteurs qui remonte des SIs des établissements
     * Puis on affecte les rôles rapporteurs et membres
     * QUID :: Président ...
     */
    public function associerJuryAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        /** @var Membre[] $membres */
        $membres = $proposition->getMembres();
        $membre = $this->membreService->getRequestedMembre($this);

        /** Ici, on prépare la liste des acteurs correspondant aux différents rôles pour le Select du formulaire
         *  d'association. On part du principe :
         *  - qu'un Rapporteur du jury est Rapporteur et Membre du jury,
         *  - qu'un Rapporteur absent est Rapporteur,
         *  - qu'un Membre du jury est Membre du jury.
         */
        $acteurs = $this->acteurService->getRepository()->findActeurByThese($these);
        $acteurs = array_filter($acteurs, function (Acteur $a) { return $a->estNonHistorise();});
        switch ($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'R';
                });
                break;
            case Membre::RAPPORTEUR_ABSENT :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'R';
                });
                break;
            case Membre::MEMBRE_JURY :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'M';
                });
                break;
        }

        $acteurs_libres = [];
        foreach ($acteurs as $acteur) {
            $libre = true;
            foreach ($membres as $membre_) {
                if ($membre_->getActeur() && $membre_->getActeur()->getId() === $acteur->getId()) {
                    $libre = false;
                    break;
                }
            }
            if ($libre) $acteurs_libres[] = $acteur;
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $acteurId = $data['acteur'];
            /** @var Acteur $acteur */
            $acteur = $this->acteurService->getRepository()->find($acteurId);

            if (! isset($acteur)) throw new RuntimeException("Aucun acteur à associer !");

            //mise à jour du membre de soutenance
            $membre->setActeur($acteur);
            $this->membreService->update($membre);
            $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

            //creation de l'utilisateur
            if ($membre->estRapporteur()) {
                $this->createUtilisateurRapporteur($acteur, $membre);
            }
        }

        return new ViewModel([
            'title' => "Association de " . $membre->getDenomination() . " à un acteur " . $this->appInfos()->getNom(),
            'acteurs' => $acteurs_libres,
            'membre' => $membre,
            'these' => $these,
        ]);
    }

    public function deassocierJuryAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membre = $this->membreService->getRequestedMembre($this);

        $acteurs = $this->acteurService->getRepository()->findActeurByThese($these);
        $acteur = null;
        foreach ($acteurs as $acteur_) {
            if ($acteur_ === $membre->getActeur()) $acteur = $acteur_;
        }
        if (!$acteur) throw new RuntimeException("Aucun acteur à deassocier !");

        //retrait dans membre de soutenance
        $membre->setActeur(null);
        $this->membreService->update($membre);
        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        if(!$these->getSource()->getImportable()) $this->acteurService->delete($acteur);

        $validation = $this->validationService->getRepository()->findValidationByTheseAndCodeAndIndividu($these, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu());
        if ($validation !== null) {
            $this->validationService->unsignEngagementImpartialite($validation);
        }

        $utilisateur = $this->membreService->getUtilisateur($membre);
        if ($utilisateur){
            try {
                $this->utilisateurService->supprimerUtilisateur($utilisateur);
            }catch (Exception $e) {
                throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
            }
        }
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /**
     * Ici on affecte au membre des individus enregistrés dans SyGAL
     */
    public function associerJuryTheseSygalAction() : ViewModel|Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membre = $this->membreService->getRequestedMembre($this);
        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete("R", $these->getEtablissement());

        $individu = $this->params()->fromQuery('individu') ?  $this->individuService->getRepository()->find($this->params()->fromQuery('individu')) : new Individu();
        $acteur = $this->acteurService->newActeur($these, $individu, $role);
        $acteur->setQualite($membre->getQualite());
        $acteur->setThese($these);

        $form = $this->acteurForm;
        $form->bind($acteur);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/associer-jury-these-sygal', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true));

        $roles = $this->applicationRoleService->getRepository()->findAll();
        /** @var ActeurFieldset $acteurFieldset */
        $acteurFieldset = $form->get('acteur');
        $acteurFieldset->setRoles($roles);

        $viewModel = new ViewModel([
            'title' => "Association de " . $membre->getDenomination() . " à un individu " . $this->appInfos()->getNom(),
            'form' => $form,
            'membre' => $membre,
            'these' => $these,
            'returnUrl' => $this->url()->fromRoute('soutenance/presoutenance/associer-jury-these-sygal', ['these' => $these->getId(), 'membre' => $membre->getId()], ["query" => ["modal" => "1"]], true)
        ]);
        $viewModel->setTemplate('these/acteur/modifier');

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var Acteur $acteur **/
        $acteur = $form->getData();
        try {
            $this->acteurService->create($acteur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

        //mise à jour du membre de soutenance
        $membre->setActeur($acteur);
        $this->membreService->update($membre);
        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        //creation de l'utilisateur
        if ($membre->estRapporteur()) {
            $this->createUtilisateurRapporteur($acteur, $membre);
        }
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    private function createUtilisateurRapporteur(Acteur $acteur, Membre $membre): void
    {
        $individu = $acteur->getIndividu() ? $acteur->getIndividu() : throw new RuntimeException("Aucun individu associé à l'acteur !");
        $these = $acteur->getThese();
        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);
        if (empty($utilisateurs)) {
            $user = $this->utilisateurService->createFromIndividu($individu, $this->membreService->generateUsername($membre), 'none');
            $user->setEmail($membre->getEmail());
            $this->userService->updateUserPasswordResetToken($user);
        }

        $this->membreService->retrieveOrCreateToken($membre);
        try {
            $proposition = $this->propositionService->findOneForThese($these);
            $notif = $this->soutenanceNotificationFactory->createNotificationConnexionRapporteur($proposition, $membre);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException) {
            // aucun destinataire, todo : cas à gérer !
        }
    }

    /**
     * Envoi des demandes d'avis de soutenance
     * /!\ si un membre est fourni alors seulement envoyé à celui-ci sinon à tous les rapporteurs
     */
    public function notifierDemandeAvisSoutenanceAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membre = $this->membreService->getRequestedMembre($this);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = [];
        if ($membre) {
            $rapporteurs[] = $membre;
        } else {
            $rapporteurs = $this->propositionService->getRapporteurs($proposition);
        }

        foreach ($rapporteurs as $rapporteur) {
            $hasRapport = ($this->avisService->getAvisByMembre($rapporteur) !== null);
            if ($hasRapport === false) {
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationDemandeAvisSoutenance($these, $rapporteur);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException) {
                    // aucun destinataire, todo : cas à gérer !
                }
            }
        }

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de rapport de pré-soutenance");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function revoquerAvisSoutenanceAction() : Response
    {
        $idAvis = $this->params()->fromRoute('avis');
        $avis = $this->avisService->getAvis($idAvis);

        $this->avisService->historiser($avis);

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $avis->getThese()->getId()], [], true);
    }

    public function indiquerDossierCompletAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $etat = $this->propositionService->findPropositionEtatByCode(Etat::COMPLET);
        $proposition->setEtat($etat);
        $this->propositionService->update($proposition);

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Dossier complet");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function feuVertAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $etat = $this->propositionService->findPropositionEtatByCode(Etat::VALIDEE);
        $proposition->setEtat($etat);
        $this->propositionService->update($proposition);

        //Met à jour le témoin soutenance autorisée pour une thèse provenant de SyGAL
        if(!$these->getSource()->getImportable()){
            $these->setSoutenanceAutorisee("O");
            $this->theseService->update($these);
        }

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationFeuVertSoutenance($proposition);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException) {
            // aucun destinataire, todo : cas à gérer !
        }
        $this->flashMessenger()
            //->setNamespace('presoutenance')
            ->addSuccessMessage("Notifications d'accord de soutenance envoyées");

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Feu vert pour la soutenance");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function stopperDemarcheAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") {
                $etat = $this->propositionService->findPropositionEtatByCode(Etat::REJETEE);
                $proposition->setEtat($etat);
                $this->propositionService->update($proposition);

                //Met à jour le témoin soutenance autorisée pour une thèse provenant de SyGAL
                if(!$these->getSource()->getImportable()){
                    $these->setSoutenanceAutorisee("N");
                    $this->theseService->update($these);
                }

                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationStopperDemarcheSoutenance($these);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException) {
                    // aucun destinataire, todo : cas à gérer !
                }
                $this->flashMessenger()
                    //->setNamespace('presoutenance')
                    ->addSuccessMessage("Notifications d'arrêt des démarches de soutenance soutenance envoyées");

                $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Annulation de la soutenance");
                exit();
            }
        }

        $vm = new ViewModel();
        if ($proposition !== null) {
            $vm->setTemplate('soutenance/default/confirmation');
            $vm->setVariables([
                'title' => "Annuler/Rejeter la proposition de soutenance",
                'text' => "L'annulation effacera le dossier de soutentance et les justificatifs associés. Êtes-vous sûr·e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('soutenance/presoutenance/stopper-demarche', ['these' => $these->getId()], [], true),
            ]);
        }
        return $vm;

    }

    /** Document pour la signature en présidence */
    #[NoReturn] public function procesVerbalSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        $exporter = new ProcesVerbalSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_proces_verbal.pdf');

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Procès verbal");
        exit;
    }

    #[NoReturn] public function avisSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        $exporter = new AvisSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_avis_soutenance.pdf');

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Avis de soutenance");
        exit;
    }

    #[NoReturn] public function rapportSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        $exporter = new RapportSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_rapport_soutenance.pdf');

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Rapport de soutenance");
        exit;
    }

    #[NoReturn] public function rapportTechniqueAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        $exporter = new RapportTechniquePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_rapport_technique.pdf');

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Rapport technique");
        exit;
    }

    /** TODO devrait être une variable VILLE_ETABLISSEMENT */
    /**
     * @param Etablissement $etablissement
     * @return string
     */
    private function getVille(Etablissement $etablissement) : string
    {
        return match ($etablissement->getStructure()->getSigle()) {
            "UCN" => "Caen",
            "URN", "INSA" => "Rouen",
            "ULHN" => "Le Havre",
            default => "Manquant",
        };
    }

    private function findSignatureEtablissement(These $these): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $these->getEtablissement()->getStructure(),
            NatureFichier::CODE_SIGNATURE_CONVOCATION,
            $these->getEtablissement());

        if ($fichier === null) {
            return null;
        }

        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            return $this->fichierStorageService->getFileForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la signature de l'établissement !", 0, $e);
        }
    }

    private function findSignatureEcoleDoctorale(These $these): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $these->getEcoleDoctorale()->getStructure(),
            NatureFichier::CODE_SIGNATURE_CONVOCATION,
            $these->getEtablissement());

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

    /** Document pour la signature en présidence */
    #[NoReturn] public function convocationsAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($these->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->export($these->getId() . '_convocation.pdf');

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Convocations");
        exit;
    }

    #[NoReturn] public function convocationDoctorantAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($these->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
        ]);
        $exporter->exportDoctorant($these->getId() . '_convocation.pdf');
        exit;
    }

    #[NoReturn] public function convocationMembreAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membre = $this->membreService->getRequestedMembre($this);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($these->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
            'date' => $dateValidation,
            'ville' => $ville,
            'signature' => $signature,
            'membre' => $membre,
        ]);
        $exporter->exportMembre($membre, $these->getId() . '_convocation.pdf');
        exit;
    }

    public function envoyerConvocationAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        //doctorant
        $doctorant = $these->getDoctorant();
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationDoctorant($doctorant, $proposition);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException) {
            // aucun destinataire, todo : cas à gérer !
        }

        //membres
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->estMembre()) {
                $email = null;
                if ($membre->getIndividu() and $membre->getIndividu()->getEmailPro()) $email = $membre->getIndividu()->getEmailPro();
                if ($email === null or trim($email) === '') $email = $membre->getEmail();
                /** @see PresoutenanceController::convocationMembreAction() */
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationMembre($membre, $proposition);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException) {
                    // aucun destinataire, todo : cas à gérer !
                }
            }
        }

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Convocations");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /** Route console ... */
    public function notifierRetardRapportPresoutenanceAction(): void
    {
        $delai = new DateInterval('P15D');
        $membres = $this->membreService->getRapporteursEnRetard($delai);

        foreach ($membres as $membre) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationNotificationRapporteurRetard($membre);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        exit();
    }

    public function transmettreDocumentsDirectionTheseAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationTransmettreDocumentsDirectionThese($these, $proposition);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException) {
            // aucun destinataire, todo : cas à gérer !
        }

        $this->horodatageService->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Transmission des documents");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /** SIMULATION DE JURY ********************************************************************************************/

    public function genererSimulationAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membres = $proposition->getMembres();

        /** @var Role $rapporteur */
        /** @var Role $membreJury */
        $rapporteur = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete('R', $these->getEtablissement());
        $membreJury = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete('M', $these->getEtablissement());

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
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new Acteur();
                    $acteur->setRole($rapporteur);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($these);
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
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Membre_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new Acteur();
                    $acteur->setRole($membreJury);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($these);
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

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function nettoyerSimulationAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->propositionService->findOneForThese($these);
        $membres = $proposition->getMembres();

        try {
            foreach ($membres as $membre) {
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->acteurService->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);
                if ($acteur !== null) {
                    $this->acteurService->getEntityManager()->remove($acteur);
                    $this->acteurService->getEntityManager()->flush($acteur);
                }

                /** @var Acteur $acteur */
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

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}
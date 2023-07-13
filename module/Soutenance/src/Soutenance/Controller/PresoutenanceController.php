<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Role;
use Application\Entity\Db\Source;
use Application\Entity\Db\TypeValidation;
use Application\Service\Role\RoleServiceAwareTrait;
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
use Soutenance\Entity\Avis;
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
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
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
    use RoleServiceAwareTrait;
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

    use DateRenduRapportFormAwareTrait;
    use AdresseSoutenanceFormAwareTrait;

    /** @var PhpRenderer */
    private $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function presoutenanceAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);
        $nbRapporteurs = count($rapporteurs);

        $renduRapport = $proposition->getRenduRapport();
        if (!$renduRapport) $this->getPropositionService()->initialisationDateRetour($proposition);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);
        $justificatifsOk = $this->getJustificatifService()->isJustificatifsOk($proposition, $justificatifs);

        $autorisation = $proposition->getJustificatif(NatureFichier::CODE_AUTORISATION_SOUTENANCE, null);
        $rapport = $proposition->getJustificatif(NatureFichier::CODE_RAPPORT_SOUTENANCE, null);
        $pv = $proposition->getJustificatif(NatureFichier::CODE_PV_SOUTENANCE, null);

        /** ==> clef: Membre->getActeur()->getIndividu()->getId() <== */
        $engagements = $this->getEngagementImpartialiteService()->getEngagmentsImpartialiteByThese($these);
        $avis = $this->getAvisService()->getAvisByThese($these);

        $validationBDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $validationPDC = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PAGE_DE_COUVERTURE, $these);

        /** Parametres ---------------------------------------------------------------------------------------------- */
        try {
            $deadline = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DELAI_RETOUR);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de la valeur d'un paramètre", 0 , $e);
        }

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'rapporteurs' => $rapporteurs,
            'engagements' => $engagements,
            'avis' => $avis,
            'tousLesEngagements' => count($engagements) === $nbRapporteurs,
            'tousLesAvis' => count($avis) === $nbRapporteurs,
            'urlFichierThese' => $this->urlFichierThese(),
            'validationBDD' => $validationBDD,
            'validationPDC' => $validationPDC,
            'justificatifsOk' => $justificatifsOk,
            'justificatifs' => $justificatifs,

            'deadline' => $deadline,

            'autorisation' => $autorisation,
            'pv' => $pv,
            'rapport' => $rapport,
        ]);
    }

    public function dateRenduRapportAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $form = $this->getDateRenduRapportForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/date-rendu-rapport', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Date de rendu");
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
        $proposition = $this->getPropositionService()->findOneForThese($these);

        /** @var Membre[] $membres */
        $membres = $proposition->getMembres();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** Ici, on prépare la liste des acteurs correspondant aux différents rôles pour le Select du formulaire
         *  d'association. On part du principe :
         *  - qu'un Rapporteur du jury est Rapporteur et Membre du jury,
         *  - qu'un Rapporteur absent est Rapporteur,
         *  - qu'un Membre du jury est Membre du jury.
         */
        $acteurs = $this->getActeurService()->getRepository()->findActeurByThese($these);
        $acteurs = array_filter($acteurs, function (Acteur $a) { return $a->estNonHistorise();});
        switch ($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return ($profil !== false AND $profil->getRoleCode() === 'R');
                });
                break;
            case Membre::RAPPORTEUR_ABSENT :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return ($profil !== false AND $profil->getRoleCode() === 'R');
                });
                break;
            case Membre::MEMBRE_JURY :
                $acteurs = array_filter($acteurs, function (Acteur $a) {
                    /** @var Profil $profil */
                    $profil = ($a->getRole()->getProfils()->first());
                    return ($profil !== false AND $profil->getRoleCode() === 'M');
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
            $acteur = $this->getActeurService()->getRepository()->find($acteurId);
            $individu = $acteur->getIndividu();

            if (! isset($acteur)) throw new RuntimeException("Aucun acteur à associer !");

            //mise à jour du membre de soutenance
            $membre->setActeur($acteur);
            $this->getMembreService()->update($membre);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");
            //creation de l'utilisateur

            if ($membre->estRapporteur()) {
                $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);
                $user = null;
                if (empty($utilisateurs)) {
                    $user = $this->utilisateurService->createFromIndividu($individu, $this->getMembreService()->generateUsername($membre), 'none');
                    $user->setEmail($membre->getEmail());
                    $this->userService->updateUserPasswordResetToken($user);
                }

                $token = $this->getMembreService()->retrieveOrCreateToken($membre);
                $url_rapporteur = $this->url()->fromRoute("soutenance/index-rapporteur", ['these' => $these->getId()], ['force_canonical' => true], true);
                $url = $this->url()->fromRoute('zfcuser/login', ['type' => 'token'], ['query' => ['token' => $token->getToken(), 'redirect' => $url_rapporteur, 'role' => $acteur->getRole()->getRoleId()], 'force_canonical' => true], true);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationConnexionRapporteur($proposition, $user, $url);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : cas à gérer !
                }
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
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $acteurs = $this->getActeurService()->getRepository()->findActeurByThese($these);
        $acteur = null;
        foreach ($acteurs as $acteur_) {
            if ($acteur_ === $membre->getActeur()) $acteur = $acteur_;
        }
        if (!$acteur) throw new RuntimeException("Aucun acteur à deassocier !");

        //retrait dans membre de soutenance
        $membre->setActeur(null);
        $this->getMembreService()->update($membre);
        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        $validation = $this->getValidationService()->getRepository()->findValidationByTheseAndCodeAndIndividu($these, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu());
        if ($validation !== null) {
            $this->getValidationService()->unsignEngagementImpartialite($validation);
        }

        $utilisateur = $this->getMembreService()->getUtilisateur($membre);
        if ($utilisateur) $this->utilisateurService->supprimerUtilisateur($utilisateur);

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /**
     * Envoi des demandes d'avis de soutenance
     * /!\ si un membre est fourni alors seulement envoyé à celui-ci sinon à tous les rapporteurs
     */
    public function notifierDemandeAvisSoutenanceAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = [];
        if ($membre) {
            $rapporteurs[] = $membre;
        } else {
            $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);
        }

        foreach ($rapporteurs as $rapporteur) {
            $hasRapport = ($this->getAvisService()->getAvisByMembre($rapporteur) !== null);
            if ($hasRapport === false) {
                $token = $this->getMembreService()->retrieveOrCreateToken($rapporteur);
                $url_rapporteur = $this->url()->fromRoute("soutenance/index-rapporteur", ['these' => $these->getId()], ['force_canonical' => true], true);
                $url = $this->url()->fromRoute('zfcuser/login', ['type' => 'token'], ['query' => ['token' => $token->getToken(), 'redirect' => $url_rapporteur, 'role' => $rapporteur->getActeur()->getRole()->getRoleId()], 'force_canonical' => true], true);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationDemandeAvisSoutenance($these, $proposition, $rapporteur, $url);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : cas à gérer !
                }
            }
        }

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de rapport de pré-soutenance");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function revoquerAvisSoutenanceAction() : Response
    {
        $idAvis = $this->params()->fromRoute('avis');
        $avis = $this->getAvisService()->getAvis($idAvis);

        $this->getAvisService()->historiser($avis);

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $avis->getThese()->getId()], [], true);
    }

    public function indiquerDossierCompletAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $etat = $this->getPropositionService()->findPropositionEtatByCode(Etat::COMPLET);
        $proposition->setEtat($etat);
        $this->getPropositionService()->update($proposition);

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Dossier complet");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function feuVertAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $etat = $this->getPropositionService()->findPropositionEtatByCode(Etat::VALIDEE);
        $proposition->setEtat($etat);
        $this->getPropositionService()->update($proposition);

        $avis = $this->getAvisService()->getAvisByThese($these);

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationFeuVertSoutenance($these, $proposition, $avis);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire, todo : cas à gérer !
        }
        $this->flashMessenger()
            //->setNamespace('presoutenance')
            ->addSuccessMessage("Notifications d'accord de soutenance envoyées");

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Feu vert pour la soutenance");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function stopperDemarcheAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") {
                $etat = $this->getPropositionService()->findPropositionEtatByCode(Etat::REJETEE);
                $proposition->setEtat($etat);
                $this->getPropositionService()->update($proposition);

                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationStopperDemarcheSoutenance($these, $proposition);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : cas à gérer !
                }
                $this->flashMessenger()
                    //->setNamespace('presoutenance')
                    ->addSuccessMessage("Notifications d'arrêt des démarches de soutenance soutenance envoyées");

                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Annulation de la soutenance");
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

    public function modifierAdresseAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $form = $this->getAdresseSoutenanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/modifier-adresse', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Adresse du lieu de soutenance");
            }
        }

        return new ViewModel([
            'title' => "Modifier l'adresse exacte de la soutenance",
            'these' => $these,
            'form' => $form,
        ]);

    }

    /** Document pour la signature en présidence */
    #[NoReturn] public function procesVerbalSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);
        $exporter = new ProcesVerbalSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_proces_verbal.pdf');

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Procès verbal");
        exit;
    }

    #[NoReturn] public function avisSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);
        $exporter = new AvisSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_avis_soutenance.pdf');

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Avis de soutenance");
        exit;
    }

    #[NoReturn] public function rapportSoutenanceAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);
        $exporter = new RapportSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_rapport_soutenance.pdf');

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Rapport de soutenance");
        exit;
    }

    #[NoReturn] public function rapportTechniqueAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);
        $exporter = new RapportTechniquePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'these' => $these,
            'informations' => $pdcData,
        ]);
        $exporter->export($these->getId() . '_rapport_technique.pdf');

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Rapport technique");
        exit;
    }

    /** TODO devrait être une variable VILLE_ETABLISSEMENT */
    /**
     * @param Etablissement $etablissement
     * @return string
     */
    private function getVille(Etablissement $etablissement) : string
    {
        switch ($etablissement->getStructure()->getSigle()) {
            case "UCN" :
                $ville = "Caen";
                break;
            case "URN" :
            case "INSA" :
                $ville = "Rouen";
                break;
            case "ULHN" :
                $ville = "Le Havre";
                break;
            default:
                $ville = "Manquant";
        }
        return $ville;
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
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
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

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Convocations");
        exit;
    }

    #[NoReturn] public function convocationDoctorantAction(): void
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
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
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $signature = $this->findSignatureEcoleDoctorale($these) ?: $this->findSignatureEtablissement($these);

        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        $validationMDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
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
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $validationMDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $avisArray = [];
        /** @var Avis $avis */
        foreach ($proposition->getAvis() as $avis) {
            if ($avis->estNonHistorise()) {
                $denomination = $avis->getMembre()->getDenomination();
                $lien = $this->url()->fromRoute('soutenance/avis-soutenance/telecharger', [
                    'these' => $these->getId(),
                    'rapporteur' => $avis->getMembre()->getId()
                    ], [
                        'force_canonical'=>true
                    ], true);
                $avisArray[$denomination] = $lien;
            }
        }

        //doctorant
        $doctorant = $these->getDoctorant();
        $email = $doctorant->getIndividu()->getEmailContact() ?:
            $doctorant->getIndividu()->getEmailPro() ?:
            $doctorant->getIndividu()->getEmailUtilisateur();
        /** @see PresoutenanceController::convocationDoctorantAction() */
        $url = $this->url()->fromRoute('soutenance/presoutenance/convocation-doctorant', ['proposition' => $proposition->getId()], ['force_canonical' => true], true);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationDoctorant($doctorant, $proposition, $dateValidation, $email, $url, $avisArray);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
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
                $url = $this->url()->fromRoute('soutenance/presoutenance/convocation-membre', ['proposition' => $proposition->getId(), 'membre' => $membre->getId()], ['force_canonical' => true], true);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationMembre($membre, $proposition, $dateValidation, $email, $url, $avisArray);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire, todo : cas à gérer !
                }
            }
        }

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Convocations");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /** Route console ... */
    public function notifierRetardRapportPresoutenanceAction()
    {
        $delai = new DateInterval('P15D');
        $membres = $this->getMembreService()->getRapporteursEnRetard($delai);
        $url = $this->url()->fromRoute('soutenances/index-rapporteur', [], ['force_canonical' => true], true);

        foreach ($membres as $membre) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationNotificationRapporteurRetard($membre, $url);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }
        exit();
    }

    public function transmettreDocumentsDirectionTheseAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationTransmettreDocumentsDirectionThese($these, $proposition);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire, todo : cas à gérer !
        }

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_NOTIFICATION, "Transmission des documents");
        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /** SIMULATION DE JURY ********************************************************************************************/

    public function genererSimulationAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membres = $proposition->getMembres();

        /** @var Role $rapporteur */
        /** @var Role $membreJury */
        $rapporteur = $this->getRoleService()->getRepository()->findOneByCodeAndStructureConcrete('R', $these->getEtablissement());
        $membreJury = $this->getRoleService()->getRepository()->findOneByCodeAndStructureConcrete('M', $these->getEtablissement());

        /** @var Source $sygal */
        $sygal = $this->sourceService->getRepository()->findOneBy(['code' => 'SYGAL::sygal']);

        /** @var Membre $membre */
        foreach($membres as $membre) {
            /** @var Individu $individu */
            $source_code_individu = 'SyGAL_Simulation_' . $membre->getId();
            $individu = $this->getIndividuService()->getRepository()->findOneBy(['sourceCode' => $source_code_individu]);
            if ($individu === null) {
                $individu = new Individu();
                $individu->setPrenom($membre->getPrenom());
                $individu->setNomUsuel($membre->getNom());
                $individu->setEmailPro($membre->getEmail());
                $individu->setSource($sygal);
                $individu->setSourceCode($source_code_individu);
                $this->getIndividuService()->getEntityManager()->persist($individu);
                $this->getIndividuService()->getEntityManager()->flush($individu);
            }

            if ($membre->estRapporteur()) {
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->getActeurService()->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new Acteur();
                    $acteur->setRole($rapporteur);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($these);
                    $acteur->setSource($sygal);
                    $acteur->setSourceCode($source_code_acteur);
                    $this->getActeurService()->getEntityManager()->persist($acteur);
                    $this->getActeurService()->getEntityManager()->flush($acteur);
                }
            }

            if ($membre->estMembre()) {
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Membre_' . $membre->getId();
                $acteur = $this->getActeurService()->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);

                if ($acteur === null) {
                    $acteur = new Acteur();
                    $acteur->setRole($membreJury);
                    $acteur->setIndividu($individu);
                    $acteur->setThese($these);
                    $acteur->setSource($sygal);
                    $acteur->setSourceCode($source_code_acteur);
                    $this->getActeurService()->getEntityManager()->persist($acteur);
                    $this->getActeurService()->getEntityManager()->flush($acteur);
                }
            }
        }

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    public function nettoyerSimulationAction() : Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $membres = $proposition->getMembres();

        try {
            foreach ($membres as $membre) {
                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Rapporteur_' . $membre->getId();
                $acteur = $this->getActeurService()->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);
                if ($acteur !== null) {
                    $this->getActeurService()->getEntityManager()->remove($acteur);
                    $this->getActeurService()->getEntityManager()->flush($acteur);
                }

                /** @var Acteur $acteur */
                $source_code_acteur = 'SyGAL_Simulation_Membre_' . $membre->getId();
                $acteur = $this->getActeurService()->getRepository()->findOneBy(['sourceCode' => $source_code_acteur]);
                if ($acteur !== null) {
                    $this->getActeurService()->getEntityManager()->remove($acteur);
                    $this->getActeurService()->getEntityManager()->flush($acteur);
                }

                /** @var Individu $source_code_individu */
                $source_code_individu = 'SyGAL_Simulation_' . $membre->getId();
                $individu = $this->getIndividuService()->getRepository()->findOneBy(['sourceCode' => $source_code_individu]);
                if ($individu !== null) {
                    $this->getActeurService()->getEntityManager()->remove($individu);
                    $this->getActeurService()->getEntityManager()->flush($individu);
                }
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en Base de donnée", 0 , $e);
        }

        return $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }
}
<?php

namespace Soutenance\Controller;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Role;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateInterval;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use HDR\Entity\Db\HDR;
use Individu\Service\IndividuServiceAwareTrait;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Exporter\AvisSoutenance\AvisSoutenancePdfExporter;
use Soutenance\Service\Exporter\Convocation\ConvocationPdfExporter;
use Soutenance\Service\Exporter\ProcesVerbal\ProcesVerbalSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportSoutenance\RapportSoutenancePdfExporter;
use Soutenance\Service\Exporter\RapportTechnique\RapportTechniquePdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use These\Entity\Db\These;
use Throwable;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuthentification\Service\Traits\UserServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;

/** @method FlashMessenger flashMessenger() */

abstract class PresoutenanceController extends AbstractSoutenanceController
{
    use HorodatageServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use AvisServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use UserServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;
    use SourceServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;

    use DateRenduRapportFormAwareTrait;

    public function dateRenduRapportAction() : ViewModel
    {
        $this->initializeFromType(false, false);

        $form = $this->getDateRenduRapportForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/date-rendu-rapport", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->propositionService->update($this->proposition);
                $this->flashMessenger()->addSuccessMessage("La date du rendu a bien été modifiée");
                $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Date de rendu");
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
        $this->initializeFromType(false, false);

        /** @var Membre[] $membres */
//        $membres = $this->proposition->getMembres();
        $membre = $this->getMembreService()->getRequestedMembre($this);

        /** Ici, on prépare la liste des acteurs correspondant aux différents rôles pour le Select du formulaire
         *  d'association. On part du principe :
         *  - qu'un Rapporteur du jury est Rapporteur et Membre du jury,
         *  - qu'un Rapporteur absent est Rapporteur,
         *  - qu'un Membre du jury est Membre du jury.
         */
        $acteurs = $this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE ?
            $this->acteurService->getRepository()->findActeurByThese($this->entity) :
            $this->acteurService->getRepository()->findActeurByHDR($this->entity);
        $acteurs = array_filter($acteurs, function (ActeurThese|ActeurHDR $a) { return $a->estNonHistorise();});
        switch ($membre->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
                $acteurs = array_filter($acteurs, function (ActeurThese|ActeurHDR $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'R';
                });
                break;
            case Membre::RAPPORTEUR_ABSENT :
                $acteurs = array_filter($acteurs, function (ActeurThese|ActeurHDR $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'R';
                });
                break;
            case Membre::MEMBRE_JURY :
                $acteurs = array_filter($acteurs, function (ActeurThese|ActeurHDR $a) {
                    /** @var Profil $profil */
                    $profil = $a->getRole()->getProfil();
                    return $profil?->getRoleCode() === 'M';
                });
                break;
        }

        $acteurs_libres = [];
        /** @var ActeurThese|ActeurHDR $acteur */
        foreach ($acteurs as $acteur) {
            // à avoir si ça pose des problèmes
            if(!$acteur->getMembre()) $acteurs_libres[] = $acteur;
//            $libre = true;
//            foreach ($membres as $membre_) {
//                $acteur_ = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre_);
////                if ($membre_->getActeur() && $membre_->getActeur()->getId() === $acteur->getId()) {
//                if ($acteur_ && $acteur_->getId() === $acteur->getId()) {
//                    $libre = false;
//                    break;
//                }
//            }
//            if ($libre) $acteurs_libres[] = $acteur;
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $acteurId = $data['acteur'];
            /** @var ActeurThese|ActeurHDR $acteur */
            $acteur = $this->acteurService->getRepository()->find($acteurId);

            if (! isset($acteur)) throw new RuntimeException("Aucun acteur à associer !");

//            //mise à jour du membre de soutenance
//            $membre->setActeur($acteur);
//            $this->getMembreService()->update($membre);
            $acteur->setMembre($membre);
            $this->acteurService->save($acteur);
            $this->flashMessenger()->addSuccessMessage($membre->getDenomination()." a bien été associé à un acteur.");
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

            //creation de l'utilisateur
            if ($membre->estRapporteur()) {
                $this->createUtilisateurRapporteur($acteur, $membre);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/presoutenance/associer-jury');
        $vm->setVariables([
            'title' => "Association de " . $membre->getDenomination() . " à un acteur " . $this->appInfos()->getNom(),
            'acteurs' => $acteurs_libres,
            'membre' => $membre,
            'entity' => $this->entity,
            'typeProposition' => $this->type
        ]);
        return $vm;
    }

    public function deassocierJuryAction() : Response
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);

//        $acteurs = $this->acteurService->getRepository()->findActeurByHDR($this->entity);
//        $a = null;
//        foreach ($acteurs as $acteur_) {
////            if ($acteur_ === $membre->getActeur()) $acteur = $acteur_;
//            if ($acteur_ === $acteur) $a = $acteur_;
//        }
        if (!$acteur) throw new RuntimeException("Aucun acteur à deassocier !");

//        //retrait dans membre de soutenance
//        $membre->setActeur(null);
//        $this->getMembreService()->update($membre);
//        $acteur->setMembre($membre);
        $acteur->setMembre(null);
        $this->acteurService->save($acteur);
        $this->flashMessenger()->addSuccessMessage($membre->getDenomination()." a bien été dissocié de son acteur.");
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");

        //si l'entité a été saisie intégralement dans SyGAL, et ne provient pas d'un SI
        if(!$this->entity->getSource()->getImportable()){
            //on historise l'acteur
            $this->acteurService->historise($acteur);

            //vérification qu'il n'y ait pas d'autre acteur associé au même individu, et qui possède le rôle Membre
            //si oui, on historise aussi
            $entity = $this->entity instanceof These ? $acteur->getThese() : $acteur->getHDR();
            $acteurMembre = $this->acteurService->getRepository()->findActeurByIndividuAndEntityAndRole($acteur->getIndividu(), $entity, Role::CODE_MEMBRE_JURY);
            if($acteurMembre) $this->acteurService->historise($acteurMembre);
        }
        $validation = $this->entity instanceof These ?
            $this->validationService->getRepository()->findValidationByTheseAndCodeAndIndividu($this->entity, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu()) :
            $this->validationService->getRepository()->findValidationByHDRAndCodeAndIndividu($this->entity, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $acteur->getIndividu());

        if ($validation !== null) {
            $this->validationService->unsignEngagementImpartialite($validation);
        }

//        $utilisateur = $this->getMembreService()->getUtilisateur($membre);
//        if ($utilisateur){
//            try {
//                $this->utilisateurService->supprimerUtilisateur($utilisateur);
//            }catch (Exception $e) {
//                throw new RuntimeException("Un problème est survenu en base de données", 0 , $e);
//            }
//        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function renseignerPresidentJuryAction() : ViewModel|Response
    {
        $this->initializeFromType();

        $role = $this->applicationRoleService->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_PRESIDENT_JURY, $this->entity->getEtablissement());

        $acteur = $this->acteurService->getRequestedActeur($this);
        $presidentJuryActeur = $this->entity instanceof These ?
            $this->acteurService->newActeurThese($this->entity, $acteur->getIndividu(), $role) :
            $this->acteurService->newActeurHDR($this->entity, $acteur->getIndividu(), $role) ;
        $presidentJuryActeur->setQualite($acteur->getQualite());
        $presidentJuryActeur->setEtablissement($acteur->getEtablissement());
        $presidentJuryActeur->setUniteRecherche($acteur->getUniteRecherche());

        try {
            $this->acteurService->save($presidentJuryActeur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

        $this->flashMessenger()->addSuccessMessage("Le président du jury a bien été enregistré.");
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['these' => $this->entity->getId()], [], true);
    }

    public function dissocierPresidentJuryAction() : ViewModel|Response
    {
        $this->initializeFromType();
        $acteur = $this->acteurService->getRequestedActeur($this);

        try {
            $this->acteurService->historise($acteur);
        }catch(RuntimeException) {
            throw new RuntimeException("Un problème est survenue lors de l'historisation en base d'un acteur");
        }

        $this->flashMessenger()->addSuccessMessage("Le président du jury a bien été supprimé.");
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Association jury");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['these' => $this->entity->getId()], [], true);
    }

    protected function createUtilisateurRapporteur(ActeurThese|ActeurHDR $acteur, Membre $membre): void
    {
        $individu = $acteur->getIndividu() ?: throw new RuntimeException("Aucun individu associé à l'acteur !");

        $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);
        if (empty($utilisateurs)) {
            $user = $this->utilisateurService->createFromIndividu($individu, $this->membreService->generateUsername($membre), 'none');
            $user->setEmail($membre->getEmail());
            $this->userService->updateUserPasswordResetToken($user);
        }

        $this->getMembreService()->retrieveOrCreateToken($membre);
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationConnexionRapporteur($this->proposition, $membre);
            $this->notifierService->trigger($notif);
            $this->flashMessenger()->addSuccessMessage("Un mail lui a été envoyé, indiquant qu'il/elle peut dès à présent se connecter sur la plateforme.");
        } catch (Throwable $e) {
            $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi du mail (à l'acteur) indiquant qu'il/elle peut dès à présent se connecter sur la plateforme. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
        }
    }

    /**
     * Envoi des demandes d'avis de soutenance
     * /!\ si un membre est fourni alors seulement envoyé à celui-ci sinon à tous les rapporteurs
     */
    public function notifierDemandeAvisSoutenanceAction() : Response
    {
        $this->initializeFromType(false, false);
        $membre = $this->membreService->getRequestedMembre($this);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = [];
        if ($membre) {
            $rapporteurs[] = $membre;
        } else {
            $rapporteurs = $this->propositionService->getRapporteurs($this->proposition);
        }

        foreach ($rapporteurs as $rapporteur) {
            $hasRapport = ($this->avisService->getAvisByMembre($rapporteur) !== null);
            if ($hasRapport === false) {
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationDemandeAvisSoutenance($this->entity, $rapporteur);
                    $this->notifierService->trigger($notif);
                    $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée.");
                } catch (Throwable $e) {
                    $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
                }
            }
        }

        $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de rapport de pré-soutenance");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function revoquerAvisSoutenanceAction() : Response
    {
        $this->initializeFromType(false, false, false);
        $idAvis = $this->params()->fromRoute('avis');
        $avis = $this->avisService->getAvis($idAvis);

        $this->avisService->historiser($avis);

        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $avis->getProposition()->getObject()->getId()], [], true);
    }

    public function indiquerDossierCompletAction() : Response
    {
        $this->initializeFromType(false, false);

        $etat = $this->propositionService->findPropositionEtatByCode(Etat::COMPLET);
        $this->proposition->setEtat($etat);
        $this->propositionService->update($this->proposition);

        $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Dossier complet");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function feuVertAction() : Response
    {
        $this->initializeFromType(false, true);

        $etat = $this->propositionService->findPropositionEtatByCode(Etat::VALIDEE);
        $this->proposition->setEtat($etat);
        $this->propositionService->update($this->proposition);

        //Met à jour le témoin soutenance autorisée pour une thèse provenant de SyGAL
        if(!$this->entity->getSource()->getImportable() && $this->entity instanceof These){
            $this->entity->setSoutenanceAutorisee("O");
            $this->entityService->update($this->entity);
        }

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationFeuVertSoutenance($this->proposition);
            $this->notifierService->trigger($notif);
            $this->flashMessenger()->addSuccessMessage("La notification indiquant que la soutenance est acceptée a bien été envoyée. 
               <br><br> <b>Envoyé :</b> aux acteurs directs, au responsable de l'ED, au responsable de l'UR");
        } catch (Throwable $e) {
            $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
        }

        $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Feu vert pour la soutenance");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function stopperDemarcheAction() : ViewModel
    {
        $this->initializeFromType(false, false);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") {
                $etat = $this->propositionService->findPropositionEtatByCode(Etat::REJETEE);
                $this->proposition->setEtat($etat);
                $this->propositionService->update($this->proposition);

                //Met à jour le témoin soutenance autorisée pour une thèse provenant de SyGAL
                if(!$this->entity->getSource()->getImportable() && $this->entity instanceof These){
                    $this->entity->setSoutenanceAutorisee("N");
                    $this->entityService->update($this->entity);
                }

                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationStopperDemarcheSoutenance($this->entity);
                    $this->notifierService->trigger($notif);
                    $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée.");
                } catch (Throwable $e) {
                    $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
                }

                $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Annulation de la soutenance");
                exit();
            }
        }

        $vm = new ViewModel();
        if ($this->proposition !== null) {
            $vm->setTemplate('soutenance/default/confirmation');
            $vm->setVariables([
                'title' => "Annuler/Rejeter la proposition de soutenance",
                'text' => "L'annulation effacera le dossier de soutentance et les justificatifs associés. Êtes-vous sûr·e de vouloir continuer ?",
                'action' => $this->url()->fromRoute("soutenance_{$this->type}/presoutenance/stopper-demarche", ['id' => $this->entity->getId()], [], true),
            ]);
        }
        return $vm;

    }

    public function deliberationJuryAction() : Response
    {
        $this->initializeFromType(false, true);

        $resultat = $this->params()->fromRoute('resultat');

        $this->entity->setResultat($resultat);
        if($this->entity instanceof These) $this->entity->setEtatThese(These::ETAT_SOUTENUE);
        if($this->entity instanceof HDR) $this->entity->setEtatHDR(HDR::ETAT_SOUTENUE);
        $this->entityService->update($this->entity);

//        try {
//            $notif = $this->soutenanceNotificationFactory->createNotificationFeuVertSoutenance($this->proposition);
//            $this->notifierService->trigger($notif);
//        } catch (\Notification\Exception\RuntimeException) {
//            // aucun destinataire, todo : cas à gérer !
//        }
//        $this->flashMessenger()
//            //->setNamespace('presoutenance')
//            ->addSuccessMessage("Le candidat a été notifié de la délibération du jury.");

        $message = $this->entity instanceof These ? "La délibération a bien été prise en compte. <br> La thèse est passée à l'état soutenue." :
            "La délibération a bien été prise en compte. <br> La HDR est passée à l'état soutenue.";
        if((int)$resultat === HDR::RESULTAT_ADMIS || (int)$resultat === These::RESULTAT_ADMIS){
            $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Délibération positive");
            $this->flashMessenger()->addSuccessMessage($message);
        }else{
            $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_ETAT, "Délibération négative");
            $this->flashMessenger()->addSuccessMessage($message);
        }
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    /** TODO devrait être une variable VILLE_ETABLISSEMENT */
    /**
     * @param Etablissement $etablissement
     * @return string
     */
    protected function getVille(Etablissement $etablissement) : string
    {
        return match ($etablissement->getStructure()->getSigle()) {
            "UCN" => "Caen",
            "URN", "INSA" => "Rouen",
            "ULHN" => "Le Havre",
            default => "Manquant",
        };
    }

    protected function findSignatureEtablissement(These|HDR $object): ?string
    {
        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $object->getEtablissement()->getStructure(),
            NatureFichier::CODE_SIGNATURE_CONVOCATION,
            $object->getEtablissement());

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

    public function envoyerConvocationAction() : Response
    {
        $this->initializeFromType(false, false);

        //doctorant
        $doctorant = $this->entity->getApprenant();
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationApprenant($doctorant, $this->proposition);
            $this->notifierService->trigger($notif);
            $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée au doctorant.");
        } catch (Throwable $e) {
            $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification au doctorant. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
        }

        //membres
        /** @var Membre $membre */
        foreach ($this->proposition->getMembres() as $membre) {
            if ($membre->estMembre()) {
                $acteur = $this->entity instanceof These ?
                    $this->acteurTheseService->getRepository()->findActeurForSoutenanceMembre($membre) :
                    $this->acteurHDRService->getRepository()->findActeurForSoutenanceMembre($membre);
                $email = null;
                if ($acteur?->getIndividu() and $acteur->getIndividu()->getEmailPro()) $email = $acteur->getIndividu()->getEmailPro();
                if ($email === null or trim($email) === '') $email = $membre->getEmail();
                try {
                    /** @see PresoutenanceController::convocationMembreAction() */
                    $notif = $this->soutenanceNotificationFactory->createNotificationEnvoiConvocationMembre($membre, $this->proposition);
                    $this->notifierService->trigger($notif);
                    $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée à {$membre->getDenomination()}.");
                } catch (Throwable $e) {
                    $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification à {$membre->getDenomination()}. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
                }
            }
        }

        $this->horodatageService->addHorodatage($this->proposition, HorodatageService::TYPE_NOTIFICATION, "Convocations");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
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
                $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée à {$membre->getDenomination()}.");
            } catch (Throwable $e) {
                $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification à {$membre->getDenomination()}. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
            }
        }
        exit();
    }

    public function notifierRapporteursEngagementImpartialiteAction(): void
    {
        $this->initializeFromType(false);
        /** @var Membre $membre */
        foreach ($this->proposition->getMembres() as $membre) {
            $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
//            if ($membre->getActeur() and $membre->estRapporteur()) {
            if ($acteur and $membre->estRapporteur()) {
                $validation = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($this->entity, $membre);
                if (!$validation) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationDemandeSignatureEngagementImpartialite($this->entity, $membre);
                        $this->notifierService->trigger($notif);
                        $this->flashMessenger()->addSuccessMessage("La notification a bien été envoyée à {$membre->getDenomination()}.");
                    } catch (Throwable $e) {
                        $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification à {$membre->getDenomination()}. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
                    }
                }
            }
        }
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_NOTIFICATION, "Demande de signature de l'engagement d'impartialité");
        $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['id' => $this->entity->getId()], [], true);
    }

    public function transmettreDocumentsDirectionAction() : Response
    {
        $this->initializeFromType();

        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationTransmettreDocumentsDirection($this->entity, $this->proposition);
            $this->notifierService->trigger($notif);
            $message = $this->proposition instanceof PropositionThese ? "Les documents ont bien été envoyés par mail <br><br> <b>Envoyé :</b> au directeur de thèse" :
                "Les documents ont bien été envoyés par mail <br><br> <b>Envoyé :</b> au garant";
            $this->flashMessenger()->addSuccessMessage($message);
        } catch (Throwable $e) {
            $this->flashMessenger()->addErrorMessage("Une erreur s'est produite lors de l'envoi de la notification. <br><br> <b>Message d'erreur</b> : ".$e->getMessage());
        }

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_NOTIFICATION, "Transmission des documents");
        return $this->redirect()->toRoute("soutenance_{$this->type}/presoutenance", ['hdr' => $this->entity->getId()], [], true);
    }

    /**
     *
     * Génération des documents liés à la présoutenance
     *
     */

    /** Document pour la signature en présidence */
    #[NoReturn] public function procesVerbalSoutenanceAction(): void
    {
        $this->initializeFromType();

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);
        $exporter = new ProcesVerbalSoutenancePdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
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
            'proposition' => $this->proposition,
            'these' => $this->entity,
            'hdr' => $this->entity,
            'informations' => $pdcData,
        ]);
        $exporter->export($this->entity->getId() . '_rapport_technique.pdf');

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Rapport technique");
        exit;
    }

    #[NoReturn] public function convocationsAction(): void
    {
        $this->initializeFromType();
        if($this->entity instanceof These){
            $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);
        }else{
            $signature = $this->findSignatureEtablissement($this->entity);
        }

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->entity instanceof These ?
            $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity) :
            $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
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

    #[NoReturn] public function convocationMembreAction(): void
    {
        $this->initializeFromType();
        $membre = $this->getMembreService()->getRequestedMembre($this);
        if($this->entity instanceof These){
            $signature = $this->findSignatureEcoleDoctorale($this->entity) ?: $this->findSignatureEtablissement($this->entity);
        }else{
            $signature = $this->findSignatureEtablissement($this->entity);
        }

        $pdcData = $this->entityService->fetchInformationsPageDeCouverture($this->entity);

        $validationMDD = $this->entity instanceof These ?
            $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity) :
            $this->validationHDRService->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $this->entity);
        $dateValidation = (!empty($validationMDD)) ? current($validationMDD)->getHistoModification() : null;

        $ville = $this->getVille($this->entity->getEtablissement());

        $exporter = new ConvocationPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'these' => $this->entity,
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
<?php

namespace Soutenance\Controller\HDR\Proposition;

use Acteur\Entity\Db\ActeurHDR;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Exception;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRService;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Assertion\HDR\PropositionHDRAssertionAwareTrait;
use Soutenance\Controller\PropositionController;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Provider\Parametre\HDR\SoutenanceParametres;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\SignaturePresident\SignaturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\ValidationHDR;

/** @method boolean isAllowed($resource, $privilege = null) */
/**
 * @property PropositionHDRService $propositionService
 * @property HDR $entity
 * @property PropositionHDR $proposition
 * @property HDRService $entityService
 */
class PropositionHDRController extends PropositionController
{
    use AvisServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;

    use RefusFormAwareTrait;

    use PropositionHDRAssertionAwareTrait;

    private PhpRenderer $renderer;

    public function setRenderer(PhpRenderer $renderer) : void
    {
        $this->renderer = $renderer;
    }

    public function propositionAction() : ViewModel|Response
    {
        $this->initializeFromType();

        if (!$this->proposition) {
            $this->proposition = $this->propositionService->create($this->entity);
            $this->propositionService->addGarantsAsMembres($this->proposition);
            return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
        }

        $message = "
                Vous n'êtes pas autorisé·e à visualiser cette proposition de soutenance. <br/><br/>
                Les personnes pouvant visualiser celle-ci sont :
                <ul>
                    <li> le·la candidat·e ; </li>  
                    <li> le·la garant·e ; </li>
                    <li> les personnes gérant cette HDR (établissement et unité de recherche associés).</li>
                </ul>
            ";
        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_VISUALISER], $message);
        if ($autorisation !== null) return $autorisation;

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();

        $currentRole = $this->userContextService->getSelectedIdentityRole();

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->propositionService->computeIndicateurForProposition($this->proposition);
        $juryOk = $this->propositionService->isJuryPropositionOk($this->proposition, $indicateurs);
        if ($juryOk === false) $indicateurs["valide"] = false;
        foreach ($this->proposition->getMembres() as $membre) {
            if ($membre->getEmail() === null) {
                $indicateurs["membresMail"]["valide"] = false;
                $indicateurs["membresMail"]["alerte"] = "Chaque membre renseigné dans la composition du jury doit avoir un mail";
                $indicateurs["valide"] = false;
                break;
            }
        }
        $isIndicateursOk = $this->propositionService->isPropositionOk($this->proposition, $indicateurs);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($this->proposition);
        $justificatifsOk = $this->getJustificatifService()->isJustificatifsOk($this->proposition, $justificatifs);

        /** Collècte des informations sur les individus liés -------------------------------------------------------- */
//        /** @var IndividuRole[] $ecoleResponsables */
//        $ecoleResponsables = [];
//        if ($this->entity->getEcoleDoctorale() !== null) {
//            $ecoleResponsables = $this->getApplicationRoleService()->findIndividuRoleByStructure($this->entity->getEcoleDoctorale()->getStructure(), null, $this->entity->getEtablissement());
//        }
        /** @var IndividuRole[] $uniteResponsables */
        $uniteResponsables = [];
        if ($this->entity->getUniteRecherche() !== null) {
            $uniteResponsables = $this->getApplicationRoleService()->findIndividuRoleByStructure($this->entity->getUniteRecherche()->getStructure(), null, $this->entity->getEtablissement());
        }
        $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($this->entity);
        $emailsAspectDoctorats = $notif->getTo();
        $informationsOk = true;
        $garants = $this->acteurService->getRepository()->findEncadrementHDR($this->entity);
        usort($garants, ActeurHDR::getComparisonFunction());
        foreach ($garants as $garant) {
            if ($garant->getIndividu()->getEmailPro() === null and $garant->getIndividu()->getComplement() === null) {
                $informationsOk = false;
                break;
            }
        }
        if (empty($uniteResponsables)) $informationsOk = false;
        foreach ($uniteResponsables as $uniteResponsable) {
            if ($uniteResponsable->getIndividu()->getEmailPro() === null and $uniteResponsable->getIndividu()->getComplement() === null) {
                $informationsOk = false;
                break;
            }
        }
//        if (empty($ecoleResponsables)) $informationsOk = false;
//        foreach ($ecoleResponsables as $ecoleResponsable) {
//            if ($ecoleResponsable->getIndividu()->getEmailPro() === null and $ecoleResponsable->getIndividu()->getComplement() === null) {
//                $informationsOk = false;
//                break;
//            }
//        }
        if (empty($emailsAspectDoctorats)) $informationsOk = false;

        /** Paramètres ---------------------------------------------------------------------------------------------- */

        try {
            $FORMULAIRE_DELOCALISATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELOCALISATION);
            $FORMULAIRE_DELEGUATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE);
            $FORMULAIRE_DEMANDE_ANGLAIS = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_REDACTION_ANGLAIS);
            $FORMULAIRE_DEMANDE_CONFIDENTIALITE = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_CONFIDENTIALITE);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de paramètre.",0,$e);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition-hdr/proposition');
        $vm->setVariables([
            'hdr' => $this->entity,
            'proposition' => $this->proposition,
            'typeProposition' => $this->type,
            'candidat' => $this->entity->getCandidat(),
            'garants' => $garants,
            'validations' => $this->propositionService->findValidationSoutenanceForHDR($this->entity),
            'validationActeur' => $this->propositionService->isValidated($this->entity, $currentIndividu, $currentRole),
            'roleCode' => $currentRole,
            'urlFichierHDR' => $this->urlFichierHDR(),
            'indicateurs' => $indicateurs,
            'juryOk' => $juryOk,
            'isIndicateursOk' => $isIndicateursOk,
            'justificatifs' => $justificatifs,
            'justificatifsOk' => $justificatifsOk,

//            'ecoleResponsables' => $ecoleResponsables,
            'uniteResponsables' => $uniteResponsables,
            'emailsAspectDoctorats' => $emailsAspectDoctorats,
            'informationsOk' => $informationsOk,
            'avis' => $this->getAvisService()->getAvisByProposition($this->proposition),
            'FORMULAIRE_DELOCALISATION' => $FORMULAIRE_DELOCALISATION,
            'FORMULAIRE_DELEGUATION' => $FORMULAIRE_DELEGUATION,
            'FORMULAIRE_DEMANDE_ANGLAIS' => $FORMULAIRE_DEMANDE_ANGLAIS,
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $FORMULAIRE_DEMANDE_CONFIDENTIALITE,

        ]);
        return $vm;
    }

    public function validerActeurAction(): ViewModel|Response
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR]);
        if ($autorisation !== null) return $autorisation;

        $validation = $this->validationService->validatePropositionSoutenance($this->entity);
        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_VALIDATION, "Acteurs directes");
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationValidationProposition($this->entity, $validation);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        $candidat = $this->entity->getCandidat();
        $validation = $this->validationService->findValidationPropositionSoutenanceByHDRAndIndividu($this->entity, $candidat->getIndividu());
        if($validation){
            $this->proposition->setEtat($this->propositionService->findPropositionEtatByCode(Etat::EN_COURS_EXAMEN));
            $this->propositionService->update($this->proposition);
        }
        /** @var ActeurHDR[] $acteurs */
        $garants = $this->entity->getActeursByRoleCode(Role::CODE_HDR_GARANT)->toArray();
        $acteurs = array_merge($garants, [$candidat]);

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            $validation = $this->validationService->findValidationPropositionSoutenanceByHDRAndIndividu($this->entity, $acteur->getIndividu());
            if ($validation === null) {
                $allValidated = false;
                break;
            }
        }
        if ($allValidated) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationUniteRechercheProposition($this->entity);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire , todo : cas à gérer !
            }
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);

    }

    public function validerStructureAction(): Response|ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_VALIDER_BDD, PropositionPrivileges::PROPOSITION_VALIDER_UR, PropositionPrivileges::PROPOSITION_VALIDER_ED]);
        if ($autorisation !== null) return $autorisation;

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $this->validationService->validateValidationUR($this->entity, $individu);
                $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_VALIDATION, "Structures");
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($this->entity);
                    if (empty($notif->getTo())) {
                        throw new RuntimeException(
                            "Aucune adresse mail trouvée pour la gestion HDR de l'établissement d'inscription '{$this->entity->getEtablissement()}'");
                    }
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                break;
            case Role::CODE_GEST_HDR:
                $this->validationService->validateValidationBDD($this->entity, $individu);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPropositionValidee($this->entity);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPresoutenance($this->entity);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }

                $this->proposition->setEtat($this->propositionService->findPropositionEtatByCode(Etat::ETABLISSEMENT));
                $this->propositionService->update($this->proposition);
                break;
            default :
                $this->validationService->validateValidationBDD($this->entity, $individu);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPropositionValidee($this->entity);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPresoutenance($this->entity);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }

                $this->proposition->setEtat($this->propositionService->findPropositionEtatByCode(Etat::ETABLISSEMENT));
                $this->propositionService->update($this->proposition);
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);

    }

    public function revoquerStructureAction(): ViewModel
    {
        $this->initializeFromType();

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role = $this->userContextService->getSelectedIdentityRole();

        /** NOTE: pas de break ici pour dévalider en cascade */
        $validations = [];
        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $validations = array_merge($validations, $this->validationService->getRepository()->findValidationByCodeAndHDR(\Validation\Entity\Db\TypeValidation::CODE_VALIDATION_PROPOSITION_UR,$this->entity));
            case Role::CODE_GEST_HDR:
                $validations = array_merge($validations, $this->validationService->getRepository()->findValidationByCodeAndHDR(\Validation\Entity\Db\TypeValidation::CODE_VALIDATION_PROPOSITION_BDD,$this->entity));
        }


        $validationsListing = "<ul>";
        if (!empty($validations)) {
            /** @var ValidationHDR $v */
            foreach ($validations as $v) {
                $validationsListing .= "<li>" . $v->getValidation()->getTypeValidation()->getLibelle() . " faite par " . $v->getHistoCreateur()->getDisplayName(). " le". $v->getHistoCreation()->format('d/m/y à H:i')."</li>";
            }
        }
        $validationsListing .= "</ul>";

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") {
                if (!empty($validations)) {
                    foreach ($validations as $v) {
                        $this->validationService->historiser($v);
                    }
                }
                $etat = $this->propositionService->findPropositionEtatByCode(Etat::EN_COURS_EXAMEN);
                $this->proposition->setEtat($etat);
                $this->propositionService->update($this->proposition);
            }
        }

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_VALIDATION, "Révocation " . $role->getCode());

        $vm = new ViewModel();
        if (!empty($validations)) {
            $vm->setTemplate('default/confirmation');
            $vm->setVariables([
                'title' => "Révocation de votre validation",
                'text' => "Cette révocation annulera les validations suivantes : " . $validationsListing . "Êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute("soutenance_{$this->type}/proposition/revoquer-structure", ['id' => $this->entity->getId()], [], true),
            ]);
        }
        return $vm;
    }

    /** Document pour la signature en présidence */
    public function signaturePresidenceAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_PRESIDENCE]);
        if ($autorisation !== null) return $autorisation;

        /** @var Membre[] $membres */
        $membres = $this->proposition->getMembres()->toArray();
        $acteursMembres = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($membres);

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Autorisation de soutenance");

        $exporter = new SignaturePresidentPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'membres' => $membres,
            'acteursMembres' => $acteursMembres,
            'validations' => $this->propositionService->findValidationSoutenanceForHDR($this->entity),
            'logos' => $this->propositionService->findLogos($this->entity),
            'libelle' => $this->propositionService->generateLibelleSignaturePresidence($this->entity),
        ]);
        $exporter->export('Document_pour_signature_-_' . $this->entity->getId() . '_-_' . str_replace(' ', '_', $this->entity->getCandidat()->getIndividu()->getNomComplet()) . '.pdf');
        exit;
    }
}
<?php

namespace Soutenance\Controller\These\PropositionThese;

use Acteur\Entity\Db\ActeurThese;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Information\Service\InformationServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Assertion\These\PropositionTheseAssertionAwareTrait;
use Soutenance\Controller\PropositionController;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\PropositionThese;
use Soutenance\Form\ChangementTitre\ChangementTitreFormAwareTrait;
use Soutenance\Form\LabelEuropeen\LabelEuropeenFormAwareTrait;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Provider\Template\PdfTemplates;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Exporter\SermentExporter\SermentPdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\SignaturePresident\SignaturePresidentPdfExporter;
use These\Entity\Db\These;
use These\Renderer\TheseTemplateVariable;
use These\Service\These\TheseService;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationThese;

/** @method boolean isAllowed($resource, $privilege = null) */
/**
 * @property PropositionTheseService $propositionService
 * @property These $entity
 * @property PropositionThese $proposition
 * @property TheseService $entityService
 */
class PropositionTheseController extends PropositionController
{
    use AvisServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use InformationServiceAwareTrait;

    use LabelEuropeenFormAwareTrait;
    use RefusFormAwareTrait;
    use ChangementTitreFormAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    use PropositionTheseAssertionAwareTrait;

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
            $this->propositionService->addDirecteursAsMembres($this->proposition);
            return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
        }

        $message = "
                Vous n'êtes pas autorisé·e à visualiser cette proposition de soutenance. <br/><br/>
                Les personnes pouvant visualiser celle-ci sont :
                <ul>
                    <li> le·la doctorant·e ; </li>  
                    <li> le·la directeur·trice et les co-directeur·trice·s ; </li>
                    <li> les personnes gérant cette thèse (école doctorale, établissement et unité de recherche associés).</li>
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
        /** @var IndividuRole[] $ecoleResponsables */
        $ecoleResponsables = [];
        if ($this->entity->getEcoleDoctorale() !== null) {
            $ecoleResponsables = $this->getApplicationRoleService()->findIndividuRoleByStructure($this->entity->getEcoleDoctorale()->getStructure(), null, $this->entity->getEtablissement());
        }
        /** @var IndividuRole[] $uniteResponsables */
        $uniteResponsables = [];
        if ($this->entity->getUniteRecherche() !== null) {
            $uniteResponsables = $this->getApplicationRoleService()->findIndividuRoleByStructure($this->entity->getUniteRecherche()->getStructure(), null, $this->entity->getEtablissement());
        }
        $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($this->entity);
        $emailsAspectDoctorats = $notif->getTo();
        $informationsOk = true;
        $directeurs = $this->acteurService->getRepository()->findEncadrementThese($this->entity);
        usort($directeurs, ActeurThese::getComparisonFunction());
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu()->getEmailPro() === null and $directeur->getIndividu()->getComplement() === null) {
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
        if (empty($ecoleResponsables)) $informationsOk = false;
        foreach ($ecoleResponsables as $ecoleResponsable) {
            if ($ecoleResponsable->getIndividu()->getEmailPro() === null and $ecoleResponsable->getIndividu()->getComplement() === null) {
                $informationsOk = false;
                break;
            }
        }
        if (empty($emailsAspectDoctorats)) $informationsOk = false;

        /** Récupération des éléments liés au bloc 'intégrité scientifique' */
        $attestationsIntegriteScientifique = $this->getJustificatifService()->getJustificatifsByPropositionAndNature($this->proposition, NatureFichier::CODE_FORMATION_INTEGRITE_SCIENTIFIQUE);

        /** Paramètres ---------------------------------------------------------------------------------------------- */

        try {
            $FORMULAIRE_DELOCALISATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELOCALISATION);
            $FORMULAIRE_DELEGUATION = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE);
            $FORMULAIRE_DEMANDE_LABEL = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_LABEL_EUROPEEN);
            $FORMULAIRE_DEMANDE_ANGLAIS = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_REDACTION_ANGLAIS);
            $FORMULAIRE_DEMANDE_CONFIDENTIALITE = $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_CONFIDENTIALITE);
        } catch (Exception $e) {
            throw new RuntimeException("Une erreur est survenue lors de la récupération de paramètre.",0,$e);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition-these/proposition');
        $vm->setVariables([
            'these' => $this->entity,
            'proposition' => $this->proposition,
            'typeProposition' => $this->type,
            'doctorant' => $this->entity->getApprenant(),
            'directeurs' => $directeurs,
            'validations' => $this->propositionService->findValidationSoutenanceForThese($this->entity),
            'validationActeur' => $this->propositionService->isValidated($this->entity, $currentIndividu, $currentRole),
            'roleCode' => $currentRole,
            'urlFichierThese' => $this->urlFichierThese(),
            'indicateurs' => $indicateurs,
            'juryOk' => $juryOk,
            'isIndicateursOk' => $isIndicateursOk,
            'justificatifs' => $justificatifs,
            'justificatifsOk' => $justificatifsOk,

            'attestationsIntegriteScientifique' => $attestationsIntegriteScientifique,

            'ecoleResponsables' => $ecoleResponsables,
            'uniteResponsables' => $uniteResponsables,
            'emailsAspectDoctorats' => $emailsAspectDoctorats,
            'informationsOk' => $informationsOk,
            'avis' => $this->getAvisService()->getAvisByProposition($this->proposition),
            'FORMULAIRE_DELOCALISATION' => $FORMULAIRE_DELOCALISATION,
            'FORMULAIRE_DELEGUATION' => $FORMULAIRE_DELEGUATION,
            'FORMULAIRE_DEMANDE_LABEL' => $FORMULAIRE_DEMANDE_LABEL,
            'FORMULAIRE_DEMANDE_ANGLAIS' => $FORMULAIRE_DEMANDE_ANGLAIS,
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $FORMULAIRE_DEMANDE_CONFIDENTIALITE,
        ]);
        return $vm;
    }

    public function labelEuropeenAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getLabelEuropeenForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/label-europeen", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $this->proposition);
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement d\'un label européen',
            'form' => $form,
        ]);
        return $vm;
    }

    public function changementTitreAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getChangementTitreForm();
        $form->setAttribute('action', $this->url()->fromRoute("soutenance_{$this->type}/proposition/changement-titre", ['id' => $this->entity->getId()], [], true));
        $form->bind($this->proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $this->proposition);
            $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Changement du titre de la thèse',
            'form' => $form,
        ]);
        return $vm;
    }

    /** Declaration sur l'honneur *************************************************************************************/

    public function declarationNonPlagiatAction(): ViewModel
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER, PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER]);
        if ($autorisation !== null) return $autorisation;

        $vm = new ViewModel();
        $vm->setTemplate("soutenance/proposition-{$this->type}/declaration-non-plagiat");
        $vm->setVariables([
            'title' => '«&nbsp;Lutte anti-plagiat : Déclaration sur l’honneur&nbsp;»',
            'these' => $this->entity,
            'validation' => null,

            /** @see PropositionTheseController::validerDeclarationNonPlagiatAction() */
            'urlValider' => $this->url()->fromRoute("soutenance_{$this->type}/proposition/declaration-non-plagiat/valider", ['id' => $this->entity->getId()], [], true),
            /** @see PropositionTheseController::refuserDeclarationNonPlagiatAction() */
            'urlRefuser' => $this->url()->fromRoute("soutenance_{$this->type}/proposition/declaration-non-plagiat/refuser", ['id' => $this->entity->getId()], [], true),
        ]);
        return $vm;
    }

    public function validerDeclarationNonPlagiatAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER]);
        if ($autorisation !== null) return $autorisation;

        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->validationService->create(\Soutenance\Provider\Validation\TypeValidation::CODE_VALIDATION_DECLARATION_HONNEUR, $this->entity, $individuUtilisateur);

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function refuserDeclarationNonPlagiatAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER]);
        if ($autorisation !== null) return $autorisation;

        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->validationService->create(\Soutenance\Provider\Validation\TypeValidation::CODE_REFUS_DECLARATION_HONNEUR, $this->entity, $individuUtilisateur);

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
    }

    public function revoquerDeclarationNonPlagiatAction()
    {
        $this->initializeFromType();

        $autorisation = $this->verifierAutorisation($this->proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER]);
        if ($autorisation !== null) return $autorisation;

        $validations = $this->validationService->getRepository()->findValidationByCodeAndThese(\Soutenance\Provider\Validation\TypeValidation::CODE_VALIDATION_DECLARATION_HONNEUR, $this->entity);
        foreach ($validations as $validation) {
            $this->validationService->historiser($validation);
        }
        $refus = $this->validationService->getRepository()->findValidationByCodeAndThese(\Soutenance\Provider\Validation\TypeValidation::CODE_REFUS_DECLARATION_HONNEUR, $this->entity);
        foreach ($refus as $refu) {
            $this->validationService->historiser($refu);
        }

        if (!$this->isAllowed($this->entity, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->propositionService->annulerValidationsForProposition($this->proposition);

        return $this->redirect()->toRoute("soutenance_{$this->type}/proposition", ['id' => $this->entity->getId()], [], true);
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

        $doctorant = $this->entity->getApprenant();
        $validation = $this->validationService->findValidationPropositionSoutenanceByTheseAndIndividu($this->entity, $doctorant->getIndividu());
        if($validation){
            $this->proposition->setEtat($this->propositionService->findPropositionEtatByCode(Etat::EN_COURS_EXAMEN));
            $this->propositionService->update($this->proposition);
        }

        /** @var ActeurThese[] $acteurs */
        $dirs = $this->entity->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $this->entity->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray(), [$doctorant]);

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            $validation = $this->validationService->findValidationPropositionSoutenanceByTheseAndIndividu($this->entity, $acteur->getIndividu());
            if($acteur instanceof Doctorant && $validation){
                $this->proposition->setEtat($this->propositionService->findPropositionEtatByCode(Etat::EN_COURS_EXAMEN));
                $this->propositionService->update($this->proposition);
            }
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
                    $notif = $this->soutenanceNotificationFactory->createNotificationEcoleDoctoraleProposition($this->entity);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $this->validationService->validateValidationED($this->entity, $individu);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($this->entity);
                    if (empty($notif->getTo())) {
                        throw new RuntimeException(
                            "Aucune adresse mail trouvée pour les aspects Doctorat de l'établissement d'inscription '{$this->entity->getEtablissement()}'");
                    }
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                break;
            case Role::CODE_BDD :
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
                throw new RuntimeException("Le role [" . $role->getCode() . "] ne peut pas valider cette proposition.");
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
                $validations = array_merge($validations, $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR,$this->entity));
            case Role::CODE_RESP_ED :
                $validations = array_merge($validations, $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED,$this->entity));
            case Role::CODE_BDD :
                $validations = array_merge($validations, $this->validationService->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD,$this->entity));
        }


        $validationsListing = "<ul>";
        if (!empty($validations)) {
            /** @var ValidationThese $v */
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

        $codirecteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($this->entity, Role::CODE_CODIRECTEUR_THESE);

        /** @var Membre[] $membres */
        $membres = $this->proposition->getMembres()->toArray();
        $acteursMembres = $this->acteurService->getRepository()->findActeursForSoutenanceMembres($membres);

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Autorisation de soutenance");

        $exporter = new SignaturePresidentPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $this->proposition,
            'membres' => $membres,
            'acteursMembres' => $acteursMembres,
            'validations' => $this->propositionService->findValidationSoutenanceForThese($this->entity),
            'logos' => $this->propositionService->findLogos($this->entity),
            'libelle' => $this->propositionService->generateLibelleSignaturePresidence($this->entity),
            'nbCodirecteur' => count($codirecteurs),
        ]);
        $exporter->export('Document_pour_signature_-_' . $this->entity->getId() . '_-_' . str_replace(' ', '_', $this->entity->getApprenant()->getIndividu()->getNomComplet()) . '.pdf');
        exit;
    }

    public function afficherSoutenancesParEcoleDoctoraleAction(): ViewModel
    {
        $this->propositionService = $this->getPropositionTheseService();
        $ecole = $this->getEcoleDoctoraleService()->getRequestedEcoleDoctorale($this);
        $soutenances = $this->propositionService->findSoutenancesAutoriseesByEcoleDoctorale($ecole);

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition-these/afficher-soutenances-par-ecole-doctorale');
        $vm->setVariables([
            'ecole' => $ecole,
            'soutenances' => $soutenances,
            'informations' => $this->informationService->getInformations(true),
        ]);
        return $vm;
    }

    /** Document pour le serment du docteur */
    public function genererSermentAction()
    {
        $this->initializeFromType();

        /** @var TheseTemplateVariable $theseTemplateVariable */
        $theseTemplateVariable = $this->templateVariablePluginManager->get('these');
        $theseTemplateVariable->setThese($this->entity);
        $vars = [
            'these' => $theseTemplateVariable,
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::SOUTENANCE_THESE_SERMENT_DU_DOCTEUR, $vars);
        $comue = $this->etablissementService->fetchEtablissementComue();

        $cheminLogoComue = ($comue) ? $this->fichierStorageService->getFileForLogoStructure($comue->getStructure()) : null;
        $cheminLogoEtablissement = ($this->entity->getEtablissement()) ? $this->fichierStorageService->getFileForLogoStructure($this->entity->getEtablissement()->getStructure()) : null;

        $this->getHorodatageService()->addHorodatage($this->proposition, HorodatageService::TYPE_EDITION, "Serment du docteur");

        $exporter = new SermentPdfExporter($this->renderer, 'A4');
        $exporter->getMpdf()->SetMargins(0, 0, 50);
        $exporter->setVars([
            'texte' => $rendu->getCorps(),
            'comue' => $comue,
            'cheminLogoComue' => $cheminLogoComue,
            'cheminLogoEtablissement' => $cheminLogoEtablissement,
        ]);
        $exporter->export($this->entity->getId() . '_serment.pdf');
        exit;
    }
}
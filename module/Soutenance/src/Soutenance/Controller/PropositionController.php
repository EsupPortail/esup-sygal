<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Information\Service\InformationServiceAwareTrait;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Assertion\PropositionAssertionAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Anglais\AnglaisFormAwareTrait;
use Soutenance\Form\ChangementTitre\ChangementTitreFormAwareTrait;
use Soutenance\Form\Confidentialite\ConfidentialiteFormAwareTrait;
use Soutenance\Form\DateLieu\DateLieuFormAwareTrait;
use Soutenance\Form\LabelEuropeen\LabelEuropeenFormAwareTrait;
use Soutenance\Form\Membre\MembreFromAwareTrait;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Provider\Parametre\SoutenanceParametres;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Provider\Template\PdfTemplates;
use Soutenance\Provider\Validation\TypeValidation;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Exporter\SermentExporter\SermentPdfExporter;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\RoleInterface;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/** @method boolean isAllowed($resource, $privilege = null) */
class PropositionController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use HorodatageServiceAwareTrait;
    use InformationServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use RenduServiceAwareTrait;
    use ValidatationServiceAwareTrait;

    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use LabelEuropeenFormAwareTrait;
    use AnglaisFormAwareTrait;
    use ConfidentialiteFormAwareTrait;
    use RefusFormAwareTrait;
    use ChangementTitreFormAwareTrait;

    use PropositionAssertionAwareTrait;

    private PhpRenderer $renderer;
    public function setRenderer(PhpRenderer $renderer) : void
    {
        $this->renderer = $renderer;
    }

    public function propositionAction() : ViewModel|Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        if (!$proposition) {
            $proposition = $this->getPropositionService()->create($these);
            $this->getPropositionService()->addDirecteursAsMembres($proposition);
            return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
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
        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_VISUALISER], $message);
        if ($autorisation !== null) return $autorisation;

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();

        $currentRole = $this->userContextService->getSelectedIdentityRole();

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->getPropositionService()->computeIndicateurForProposition($proposition);
        $juryOk = $this->getPropositionService()->isJuryPropositionOk($proposition, $indicateurs);
        if ($juryOk === false) $indicateurs["valide"] = false;
        $isOk = $this->getPropositionService()->isPropositionOk($proposition, $indicateurs);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);
        $justificatifsOk = $this->getJustificatifService()->isJustificatifsOk($proposition, $justificatifs);


        /** Collècte des informations sur les individus liés -------------------------------------------------------- */
        /** @var IndividuRole[] $ecoleResponsables */
        $ecoleResponsables = [];
        if ($these->getEcoleDoctorale() !== null) {
            $ecoleResponsables = $this->getRoleService()->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure(), null, $these->getEtablissement());
        }
        /** @var IndividuRole[] $uniteResponsables */
        $uniteResponsables = [];
        if ($these->getUniteRecherche() !== null) {
            $uniteResponsables = $this->getRoleService()->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure(), null, $these->getEtablissement());
        }
        $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($these);
        $emailsAspectDoctorats = $notif->getTo();
        $informationsOk = true;
        $directeurs = $this->getActeurService()->getRepository()->findEncadrementThese($these);
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
        $attestationsIntegriteScientifique = $this->getJustificatifService()->getJustificatifsByPropositionAndNature($proposition, NatureFichier::CODE_FORMATION_INTEGRITE_SCIENTIFIQUE);

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

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'doctorant' => $these->getDoctorant(),
            'directeurs' => $directeurs,
            'validations' => $this->getPropositionService()->findValidationSoutenanceForThese($these),
            'validationActeur' => $this->getPropositionService()->isValidated($these, $currentIndividu, $currentRole),
            'roleCode' => $currentRole,
            'urlFichierThese' => $this->urlFichierThese(),
            'indicateurs' => $indicateurs,
            'juryOk' => $juryOk,
            'isOk' => $isOk,
            'justificatifs' => $justificatifs,
            'justificatifsOk' => $justificatifsOk,

            'attestationsIntegriteScientifique' => $attestationsIntegriteScientifique,

            'ecoleResponsables' => $ecoleResponsables,
            'uniteResponsables' => $uniteResponsables,
            'emailsAspectDoctorats' => $emailsAspectDoctorats,
            'informationsOk' => $informationsOk,
            'avis' => $this->getAvisService()->getAvisByThese($these),

            'FORMULAIRE_DELOCALISATION' => $FORMULAIRE_DELOCALISATION,
            'FORMULAIRE_DELEGUATION' => $FORMULAIRE_DELEGUATION,
            'FORMULAIRE_DEMANDE_LABEL' => $FORMULAIRE_DEMANDE_LABEL,
            'FORMULAIRE_DEMANDE_ANGLAIS' => $FORMULAIRE_DEMANDE_ANGLAIS,
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $FORMULAIRE_DEMANDE_CONFIDENTIALITE,

        ]);
    }

    public function modifierDateLieuAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getDateLieuForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-date-lieu', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->update($request, $form, $proposition);
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Date et lieu");
                $this->getPropositionService()->initialisationDateRetour($proposition);
                if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form' => $form,
            'title' => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-membre', ['these' => $these->getId()], [], true));

        $new = false;
        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre === null) {
            $membre = new Membre();
            $membre->setProposition($proposition);
            $new = true;
        }
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($new !== true) {
                    $this->getMembreService()->update($membre);
                } else {
                    $this->getMembreService()->create($membre);
                }
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Jury");
                if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setVariables([
            'form' => $form,
            'these' => $these,
            'title' => 'Renseigner les informations sur un membre du jury',
        ]);
        return $vm;
    }

    public function effacerMembreAction() : ViewModel|Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre) {
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($membre->getProposition());
            $this->getMembreService()->delete($membre);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Jury");
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function labelEuropeenAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getLabelEuropeenForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/label-europeen', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement d\'un label europeen',
            'form' => $form,
        ]);
        return $vm;
    }

    public function anglaisAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getAnglaisForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/anglais', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement de l\'utilisation de l\'anglais',
            'form' => $form,
        ]);
        return $vm;
    }

    public function confidentialiteAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/confidentialite', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition/confidentialite');
        $vm->setVariables([
            'title' => 'Renseignement des informations relatives à la confidentialité',
            'form' => $form,
            'these' => $these,
        ]);
        return $vm;
    }

    public function changementTitreAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getChangementTitreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/changement-titre', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_MODIFICATION, "Informations complémentaires");
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Changement du titre de la thèse',
            'form' => $form,
        ]);
        return $vm;
    }

    public function validerActeurAction(): ViewModel|Response
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR]);
        if ($autorisation !== null) return $autorisation;

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_VALIDATION, "Acteurs directes");
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationValidationProposition($these, $validation);
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        $doctorant = $these->getDoctorant();

        /** @var Acteur[] $acteurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray(), [$doctorant]);

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            if ($this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $acteur->getIndividu()) === null) {
                $allValidated = false;
                break;
            }
        }
        if ($allValidated) {
            try {
                $notif = $this->soutenanceNotificationFactory->createNotificationUniteRechercheProposition($these);
                $this->notifierService->trigger($notif);
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire , todo : cas à gérer !
            }
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function validerStructureAction(): Response|ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_VALIDER_BDD, PropositionPrivileges::PROPOSITION_VALIDER_UR, PropositionPrivileges::PROPOSITION_VALIDER_ED]);
        if ($autorisation !== null) return $autorisation;

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $this->getValidationService()->validateValidationUR($these, $individu);
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_VALIDATION, "Structures");
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationEcoleDoctoraleProposition($these);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $this->getValidationService()->validateValidationED($these, $individu);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationBureauDesDoctoratsProposition($these);
                    if (empty($notif->getTo())) {
                        throw new RuntimeException(
                            "Aucune adresse mail trouvée pour les aspects Doctorat de l'établissement d'inscription '{$these->getEtablissement()}'");
                    }
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                break;
            case Role::CODE_BDD :
                $this->getValidationService()->validateValidationBDD($these, $individu);
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPropositionValidee($these);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationPresoutenance($these);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }

                $proposition = $this->getPropositionService()->findOneForThese($these);
                $proposition->setEtat($this->getPropositionService()->findPropositionEtatByCode(Etat::ETABLISSEMENT));
                $this->getPropositionService()->update($proposition);
                break;
            default :
                throw new RuntimeException("Le role [" . $role->getCode() . "] ne peut pas valider cette proposition.");
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function revoquerStructureAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role = $this->userContextService->getSelectedIdentityRole();

        /** NOTE: pas de break ici pour dévalider en cascade */
        $validations = [];
        switch ($role->getCode()) {
            case Role::CODE_RESP_UR :
                $validations = array_merge($validations, $this->getValidationService()->getRepository()->findValidationByCodeAndThese(\Application\Entity\Db\TypeValidation::CODE_VALIDATION_PROPOSITION_UR,$these));
            case Role::CODE_RESP_ED :
                $validations = array_merge($validations, $this->getValidationService()->getRepository()->findValidationByCodeAndThese(\Application\Entity\Db\TypeValidation::CODE_VALIDATION_PROPOSITION_ED,$these));
            case Role::CODE_BDD :
                $validations = array_merge($validations, $this->getValidationService()->getRepository()->findValidationByCodeAndThese(\Application\Entity\Db\TypeValidation::CODE_VALIDATION_PROPOSITION_BDD,$these));
        }


        $validationsListing = "<ul>";
        if (!empty($validations)) {
            /** @var Validation $v */
            foreach ($validations as $v) {
                $validationsListing .= "<li>" . $v->getTypeValidation()->getLibelle() . " faite par" . $v->getHistoCreateur()->getDisplayName(). " le". $v->getHistoCreation()->format('d/m/y à H:i')."</li>";
            }
        }
        $validationsListing .= "</ul>";

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") {
                if (!empty($validations)) {
                    foreach ($validations as $v) {
                        $this->getValidationService()->historise($v);
                    }
                }
                $etat = $this->getPropositionService()->findPropositionEtatByCode(Etat::EN_COURS);
                $proposition->setEtat($etat);
                $this->getPropositionService()->update($proposition);
            }
        }

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_VALIDATION, "Révocation " . $role->getCode());

        $vm = new ViewModel();
        if (!empty($validations)) {
            $vm->setTemplate('default/confirmation');
            $vm->setVariables([
                'title' => "Révocation de votre validation",
                'text' => "Cette révocation annulera les validations suivantes : " . $validationsListing . "Êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('soutenance/proposition/revoquer-structure', ["these" => $these->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function refuserStructureAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_VALIDER_BDD, PropositionPrivileges::PROPOSITION_VALIDER_UR, PropositionPrivileges::PROPOSITION_VALIDER_ED]);
        if ($autorisation !== null) return $autorisation;

        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser-structure', ['these' => $these->getId()], [], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->getPropositionService()->annulerValidationsForProposition($proposition);
                $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_VALIDATION, "Structures");

                $currentUser = $this->userContextService->getIdentityIndividu();
                /** @var RoleInterface $currentRole */
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                try {
                    $notif = $this->soutenanceNotificationFactory->createNotificationRefusPropositionSoutenance($these, $currentUser, $currentRole, $data['motif']);
                    $this->notifierService->trigger($notif);
                } catch (\Notification\Exception\RuntimeException $e) {
                    // aucun destinataire , todo : cas à gérer !
                }
            }
        }

        return new ViewModel([
            'title' => "Motivation du refus de la proposition de soutenance",
            'form' => $form,
            'these' => $these,
        ]);
    }

    /** Document pour la signature en présidence */
    public function signaturePresidenceAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_PRESIDENCE]);
        if ($autorisation !== null) return $autorisation;


        $codirecteurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);


        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Autorisation de soutenance");

        $exporter = new SiganturePresidentPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'validations' => $this->getPropositionService()->findValidationSoutenanceForThese($these),
            'logos' => $this->getPropositionService()->findLogosForThese($these),
            'libelle' => $this->getPropositionService()->generateLibelleSignaturePresidenceForThese($these),
            'nbCodirecteur' => count($codirecteurs),
        ]);
        $exporter->export('Document_pour_signature_-_' . $these->getId() . '_-_' . str_replace(' ', '_', $these->getDoctorant()->getIndividu()->getNomComplet()) . '.pdf');
        exit;
    }

    public function toggleSursisAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $sursis = $proposition->hasSursis();
        $proposition->setSurcis(!$sursis);
        $this->getPropositionService()->update($proposition);

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_ETAT, "Sursis");

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    /**
     * @param Request $request
     * @param Form $form
     * @param Proposition $proposition
     * @return Proposition
     */
    private function update(Request $request, Form $form, Proposition $proposition): Proposition
    {
        $data = $request->getPost();
        $form->setData($data);
        if ($form->isValid()) {
            $this->getPropositionService()->update($proposition);
        }
        return $proposition;
    }

    public function suppressionAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        //detruire la  || historiser si on histo
        $this->getPropositionService()->historise($proposition);

        //historiser les validations
        $validations = $this->getValidationService()->getRepository()->findValidationsByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
        }

        return $this->redirect()->toRoute('soutenance', [], [], true);
    }

    public function afficherSoutenancesParEcoleDoctoraleAction(): ViewModel
    {
        $ecole = $this->getEcoleDoctoraleService()->getRequestedEcoleDoctorale($this);
        $soutenances = $this->getPropositionService()->findSoutenancesAutoriseesByEcoleDoctorale($ecole);

        return new ViewModel([
            'ecole' => $ecole,
            'soutenances' => $soutenances,
            'informations' => $this->informationService->getInformations(true),
        ]);
    }

    /** Declaration sur l'honneur *************************************************************************************/

    public function declarationNonPlagiatAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER, PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER]);
        if ($autorisation !== null) return $autorisation;

        return new ViewModel([
            'title' => '«&nbsp;Lutte anti-plagiat : Déclaration sur l’honneur&nbsp;»',
            'these' => $these,
            'validation' => null,

            /** @see PropositionController::validerDeclarationNonPlagiatAction() */
            'urlValider' => $this->url()->fromRoute('soutenance/proposition/declaration-non-plagiat/valider', ['these' => $these->getId()], [], true),
            /** @see PropositionController::refuserDeclarationNonPlagiatAction() */
            'urlRefuser' => $this->url()->fromRoute('soutenance/proposition/declaration-non-plagiat/refuser', ['these' => $these->getId()], [], true),
        ]);
    }

    public function validerDeclarationNonPlagiatAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER]);
        if ($autorisation !== null) return $autorisation;

        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->getValidationService()->create(TypeValidation::CODE_VALIDATION_DECLARATION_HONNEUR, $these, $individuUtilisateur);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function refuserDeclarationNonPlagiatAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER]);
        if ($autorisation !== null) return $autorisation;

        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->getValidationService()->create(TypeValidation::CODE_REFUS_DECLARATION_HONNEUR, $these, $individuUtilisateur);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function revoquerDeclarationNonPlagiatAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER]);
        if ($autorisation !== null) return $autorisation;

        $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_DECLARATION_HONNEUR, $these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
        }
        $refus = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_REFUS_DECLARATION_HONNEUR, $these);
        foreach ($refus as $refu) {
            $this->getValidationService()->historise($refu);
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    /** Vue ***********************************************************************************************************/

    public function generateViewDateLieuAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'these' => $these,
            'proposition' => $proposition,
            'FORMULAIRE_DELOCALISATION' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELOCALISATION),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
        ]);
        return $vm;
    }

    public function generateViewJuryAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->getPropositionService()->computeIndicateurForProposition($proposition);
        $juryOk = $this->getPropositionService()->isJuryPropositionOk($proposition, $indicateurs);
        if ($juryOk === false) $indicateurs["valide"] = false;
        //$isOk = $this->getPropositionService()->isOk($proposition, $indicateurs);

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'these' => $these,
            'proposition' => $proposition,
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
            'indicateurs' => $indicateurs,
        ]);
        return $vm;
    }

    public function generateViewInformationsAction(): ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);


        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'these' => $these,
            'proposition' => $proposition,
            'FORMULAIRE_DEMANDE_LABEL' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_LABEL_EUROPEEN),
            'FORMULAIRE_DEMANDE_ANGLAIS' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_REDACTION_ANGLAIS),
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_CONFIDENTIALITE),
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
        ]);
        return $vm;
    }

    /** Error *********************************************************************************************************/

    /**
     * @param Proposition $proposition
     * @param array $privilieges
     * @param string|null $message
     * @return ViewModel|null
     */
    private function verifierAutorisation(Proposition $proposition, array $privilieges, ?string $message = null): ?ViewModel
    {
        $authorized = false;
        foreach ($privilieges as $priviliege) {
            $authorized = $this->getPropositionAssertion()->computeValeur(null, $proposition, $priviliege);
            if ($authorized === true) break;
        }
        if ($authorized === false) {
            $vm = new ViewModel();
            $vm->setTemplate('soutenance/error/403');
            $vm->setVariables(['message' => $message]);
            return $vm;
        }
        return null;
    }

    /** Document pour le serment du docteur */
    public function genererSermentAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $vars = [
            'doctorant' => $these->getDoctorant(),
            'proposition' => $proposition,
            'these' => $these,
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::SERMENT_DU_DOCTEUR, $vars);
        $comue = $this->etablissementService->fetchEtablissementComue();

        $cheminLogoComue = ($comue) ? $this->fichierStorageService->getFileForLogoStructure($comue->getStructure()) : null;
        $cheminLogoEtablissement = ($these->getEtablissement()) ? $this->fichierStorageService->getFileForLogoStructure($these->getEtablissement()->getStructure()) : null;

        $this->getHorodatageService()->addHorodatage($proposition, HorodatageService::TYPE_EDITION, "Serment du docteur");

        $exporter = new SermentPdfExporter($this->renderer, 'A4');
        $exporter->getMpdf()->SetMargins(0, 0, 50);
        $exporter->setVars([
            'texte' => $rendu->getCorps(),
            'comue' => $comue,
            'cheminLogoComue' => $cheminLogoComue,
            'cheminLogoEtablissement' => $cheminLogoEtablissement,
        ]);
        $exporter->export($these->getId() . '_serment.pdf');
        exit;
    }

    /** Gestion des horodatages d'une proposition **************************/

    public function horodatagesAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $horodatages = $proposition->getHorodatages();

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'horodatages' => $horodatages,
        ]);
    }
}
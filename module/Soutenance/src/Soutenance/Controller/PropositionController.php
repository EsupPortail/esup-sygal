<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Information\Service\InformationServiceAwareTrait;
use These\Entity\Db\Acteur;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use These\Service\Acteur\ActeurServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Assertion\PropositionAssertionAwareTrait;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Evenement;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Parametre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Anglais\AnglaisFormAwareTrait;
use Soutenance\Form\ChangementTitre\ChangementTitreFormAwareTrait;
use Soutenance\Form\Confidentialite\ConfidentialiteFormAwareTrait;
use Soutenance\Form\DateLieu\DateLieuFormAwareTrait;
use Soutenance\Form\LabelEuropeen\LabelEuropeenFormAwareTrait;
use Soutenance\Form\Membre\MembreFromAwareTrait;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Provider\Validation\TypeValidation;
use Soutenance\Service\Evenement\EvenementServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\RoleInterface;

/** @method boolean isAllowed($resource, $privilege = null) */
class PropositionController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EvenementServiceAwareTrait;
    use InformationServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidatationServiceAwareTrait;

    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use LabelEuropeenFormAwareTrait;
    use AnglaisFormAwareTrait;
    use ConfidentialiteFormAwareTrait;
    use RefusFormAwareTrait;
    use ChangementTitreFormAwareTrait;

    use PropositionAssertionAwareTrait;

    /** @var PhpRenderer */
    private $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function propositionAction()
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

        /** Adresse des formulaires --------------------------------------------------------------------------------- */
        $parametres = $this->getParametreService()->getParametresAsArray();

        /** Collècte des informations sur les individus liés -------------------------------------------------------- */
        /** @var IndividuRole[] $ecoleResponsables */
        $ecoleResponsables = [];
        if ($these->getEcoleDoctorale() !== null) {
            $ecoleResponsables = $this->getRoleService()->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
            $ecoleResponsables = array_filter($ecoleResponsables, function (IndividuRole $ir) use ($these) {
                return $ir->getIndividu()->getEtablissement() and $ir->getIndividu()->getEtablissement()->getId() === $these->getEtablissement()->getId();
            });
        }
        /** @var IndividuRole[] $uniteResponsables */
        $uniteResponsables = [];
        if ($these->getUniteRecherche() !== null) {
            $uniteResponsables = $this->getRoleService()->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
            $uniteResponsables = array_filter($uniteResponsables, function (IndividuRole $ir) use ($these) {
                return $ir->getIndividu()->getEtablissement() and $ir->getIndividu()->getEtablissement()->getId() === $these->getEtablissement()->getId();
            });
        }
        /** @var IndividuRole[] $etablissementResponsables */
        $etablissementResponsables = [];
        if ($these->getEtablissement() !== null) {
            $etablissementResponsables = $this->roleService->findIndividuRoleByStructure($these->getEtablissement()->getStructure());
            $etablissementResponsables = array_filter($etablissementResponsables, function (IndividuRole $ir) {
                return $ir->getRole()->getCode() === Role::CODE_BDD;
            });
            $etablissementResponsables = array_filter($etablissementResponsables, function (IndividuRole $ir) use ($these) {
                return $ir->getIndividu()->getEtablissement() and $ir->getIndividu()->getEtablissement()->getId() === $these->getEtablissement()->getId();
            });
        }
        $informationsOk = true;
        $directeurs = $this->getActeurService()->getRepository()->findEncadrementThese($these);
        foreach ($directeurs as $directeur) {
            if ($directeur->getIndividu()->getEmail() === null AND $directeur->getIndividu()->getComplement() === null) {
                $informationsOk = false;
                break;
            }
        }
        if (empty($uniteResponsables)) $informationsOk = false;
        foreach ($uniteResponsables as $uniteResponsable) {
            if ($uniteResponsable->getIndividu()->getEmail() === null) { $informationsOk = false; break;}
        }
        if (empty($ecoleResponsables)) $informationsOk = false;
        foreach ($ecoleResponsables as $ecoleResponsable) {
            if ($ecoleResponsable->getIndividu()->getEmail() === null) { $informationsOk = false; break;}
        }
        if (empty($etablissementResponsables)) $informationsOk = false;
        foreach ($etablissementResponsables as $etablissementResponsable) {
            if ($etablissementResponsable->getIndividu()->getEmail() === null) { $informationsOk = false; break;}
        }
        /** @var Individu $individu */
        foreach (array_merge($ecoleResponsables, $uniteResponsables, $etablissementResponsables) as $ecoleResponsable) {
            $individu = $ecoleResponsable->getIndividu();
            if ($individu->getEmail() === null AND $individu->getComplement() === null) {
                $informationsOk = false;
                break;
            }
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
            'signatures' => $this->getEvenementService()->getEvenementsByPropositionAndType($proposition, Evenement::EVENEMENT_SIGNATURE),

            'ecoleResponsables' => $ecoleResponsables,
            'uniteResponsables' => $uniteResponsables,
            'etablissementResponsables' => $etablissementResponsables,
            'informationsOk' => $informationsOk,

            'FORMULAIRE_DELOCALISATION' => $parametres[Parametre::CODE_FORMULAIRE_DELOCALISATION],
            'FORMULAIRE_DELEGUATION' => $parametres[Parametre::CODE_FORMULAIRE_DELEGUATION],
            'FORMULAIRE_DEMANDE_LABEL' => $parametres[Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN],
            'FORMULAIRE_DEMANDE_ANGLAIS' => $parametres[Parametre::CODE_FORMULAIRE_THESE_ANGLAIS],
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $parametres[Parametre::CODE_FORMULAIRE_CONFIDENTIALITE],

        ]);
    }

    public function modifierDateLieuAction() : ViewModel
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
            $this->update($request, $form, $proposition);
            $this->getPropositionService()->initialisationDateRetour($proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form' => $form,
            'title' => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction() : ViewModel
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

    public function effacerMembreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_MODIFIER, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION]);
        if ($autorisation !== null) return $autorisation;

        $membre = $this->getMembreService()->getRequestedMembre($this);
        if ($membre) {
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidationsForProposition($membre->getProposition());
            $this->getMembreService()->delete($membre);
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function labelEuropeenAction() : ViewModel
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

    public function anglaisAction() : ViewModel
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

    public function confidentialiteAction() : ViewModel
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

    public function changementTitreAction() : ViewModel
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

    public function validerActeurAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $autorisation = $this->verifierAutorisation($proposition, [PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR]);
        if ($autorisation !== null) return $autorisation;

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierSoutenanceService()->triggerValidationProposition($these, $validation);

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
        if ($allValidated) $this->getNotifierSoutenanceService()->triggerNotificationUniteRechercheProposition($these);

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function validerStructureAction()
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
                $this->getNotifierSoutenanceService()->triggerNotificationEcoleDoctoraleProposition($these);
                break;
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
                $this->getValidationService()->validateValidationED($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationBureauDesDoctoratsProposition($these);
                break;
            case Role::CODE_BDD :
                $this->getValidationService()->validateValidationBDD($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationPropositionValidee($these);
                $this->getNotifierSoutenanceService()->triggerNotificationPresoutenance($these);

                $proposition = $this->getPropositionService()->findOneForThese($these);
                $proposition->setEtat($this->getPropositionService()->findPropositionEtatByCode(Etat::ETABLISSEMENT));
                $this->getPropositionService()->update($proposition);
                break;
            default :
                throw new RuntimeException("Le role [" . $role->getCode() . "] ne peut pas valider cette proposition.");
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction() : ViewModel
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
                $currentUser = $this->userContextService->getIdentityIndividu();
                /** @var RoleInterface $currentRole */
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                $this->getNotifierSoutenanceService()->triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $data['motif']);
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


        $this->getEvenementService()->ajouterEvenement($proposition, Evenement::EVENEMENT_SIGNATURE);

        $exporter = new SiganturePresidentPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'validations' => $this->getPropositionService()->findValidationSoutenanceForThese($these),
            'logos' => $this->getPropositionService()->findLogosForThese($these),
            'libelle' => $this->getPropositionService()->generateLibelleSignaturePresidenceForThese($these),
            'nbCodirecteur' => count($codirecteurs),
        ]);
        $exporter->export('Document_pour_signature_-_'.$these->getId().'_-_'.str_replace(' ','_',$these->getDoctorant()->getIndividu()->getNomComplet()).'.pdf');
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

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    /**
     * @param Request $request
     * @param Form $form
     * @param Proposition $proposition
     * @return Proposition
     */
    private function update(Request $request, Form $form, Proposition $proposition) : Proposition
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

    public function afficherSoutenancesParEcoleDoctoraleAction() : ViewModel
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

    public function declarationNonPlagiatAction() : ViewModel
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
        foreach ($validations as $validation) { $this->getValidationService()->historise($validation); }
        $refus = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_REFUS_DECLARATION_HONNEUR, $these);
        foreach ($refus as $refu) { $this->getValidationService()->historise($refu); }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    /** Vue ***********************************************************************************************************/

    public function generateViewDateLieuAction()  : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $parametres = $this->getParametreService()->getParametresAsArray();

        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'these' => $these,
            'proposition' => $proposition,
            'FORMULAIRE_DELOCALISATION' => $parametres[Parametre::CODE_FORMULAIRE_DELOCALISATION],
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
        ]);
        return $vm;
    }
    public function generateViewJuryAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $parametres = $this->getParametreService()->getParametresAsArray();

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
            'FORMULAIRE_DELEGUATION' => $parametres[Parametre::CODE_FORMULAIRE_DELEGUATION],
            'canModifier' => $this->isAllowed(PropositionPrivileges::getResourceId(PropositionPrivileges::PROPOSITION_MODIFIER)),
            'indicateurs' => $indicateurs,
        ]);
        return $vm;
    }
    public function generateViewInformationsAction() : ViewModel
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $parametres = $this->getParametreService()->getParametresAsArray();


        $vm = new ViewModel();
        $vm->setTerminal(true);
        $vm->setVariables([
            'these' => $these,
            'proposition' => $proposition,
            'FORMULAIRE_DEMANDE_LABEL' => $parametres[Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN],
            'FORMULAIRE_DEMANDE_ANGLAIS' => $parametres[Parametre::CODE_FORMULAIRE_THESE_ANGLAIS],
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $parametres[Parametre::CODE_FORMULAIRE_CONFIDENTIALITE],
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
    private function verifierAutorisation(Proposition  $proposition, array $privilieges, ?string $message = null) : ?ViewModel
    {
        $authorized = false;
        foreach ($privilieges as $priviliege) {
            $authorized = $this->getPropositionAssertion()->computeValeur(null, $proposition, $priviliege);
            if ($authorized === true) break;
        }
        if ($authorized === false) {
            $vm = new ViewModel();
            $vm->setTemplate('soutenance/error/403');
            $vm->setVariables(['message' => $message ]);
            return $vm;
        }
        return null;
    }
}
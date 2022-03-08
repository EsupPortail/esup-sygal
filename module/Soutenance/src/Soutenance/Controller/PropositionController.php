<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Evenement;
use Soutenance\Entity\Etat;
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
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/** @method boolean isAllowed($resource, $privilege = null) */
class PropositionController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EvenementServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidatationServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use ParametreServiceAwareTrait;

    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use LabelEuropeenFormAwareTrait;
    use AnglaisFormAwareTrait;
    use ConfidentialiteFormAwareTrait;
    use RefusFormAwareTrait;
    use ChangementTitreFormAwareTrait;

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
        $proposition = $this->getPropositionService()->findByThese($these);

        if (!$proposition) {
            $proposition = $this->getPropositionService()->create($these);
            $this->getPropositionService()->addDirecteursAsMembres($proposition);
            return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();

        /** @var Role $currentRole */
        $currentRole = $this->userContextService->getSelectedIdentityRole();

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->getPropositionService()->computeIndicateur($proposition);
        $juryOk = $this->getPropositionService()->juryOk($proposition, $indicateurs);
        if ($juryOk === false) $indicateurs["valide"] = false;
        $isOk = $this->getPropositionService()->isOk($proposition, $indicateurs);

        /** Justificatifs attendus ---------------------------------------------------------------------------------- */
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition);
        $justificatifsOk = $this->getJustificatifService()->isJustificatifsOk($proposition, $justificatifs);

        /** Adresse des formulaires --------------------------------------------------------------------------------- */
        $parametres = $this->getParametreService()->getParametresAsArray();

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'doctorant' => $these->getDoctorant(),
            'directeurs' => $this->getActeurService()->getRepository()->findEncadrementThese($these),
            'validations' => $this->getPropositionService()->getValidationSoutenance($these),
            'validationActeur' => $this->getPropositionService()->isValidated($these, $currentIndividu, $currentRole),
            'roleCode' => $currentRole,
            'urlFichierThese' => $this->urlFichierThese(),
            'indicateurs' => $indicateurs,
            'juryOk' => $juryOk,
            'isOk' => $isOk,
            'justificatifs' => $justificatifs,
            'justificatifsOk' => $justificatifsOk,
            'signatures' => $this->getEvenementService()->getEvenementsByPropositionAndType($proposition, Evenement::EVENEMENT_SIGNATURE),

            'FORMULAIRE_DELOCALISATION' => $parametres[Parametre::CODE_FORMULAIRE_DELOCALISATION],
            'FORMULAIRE_DELEGUATION' => $parametres[Parametre::CODE_FORMULAIRE_DELEGUATION],
            'FORMULAIRE_DEMANDE_LABEL' => $parametres[Parametre::CODE_FORMULAIRE_LABEL_EUROPEEN],
            'FORMULAIRE_DEMANDE_ANGLAIS' => $parametres[Parametre::CODE_FORMULAIRE_THESE_ANGLAIS],
            'FORMULAIRE_DEMANDE_CONFIDENTIALITE' => $parametres[Parametre::CODE_FORMULAIRE_CONFIDENTIALITE],

        ]);
    }

    public function generateViewDateLieuAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
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

    public function generateViewJuryAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
        $parametres = $this->getParametreService()->getParametresAsArray();

        /** Indicateurs --------------------------------------------------------------------------------------------- */
        $indicateurs = $this->getPropositionService()->computeIndicateur($proposition);
        $juryOk = $this->getPropositionService()->juryOk($proposition, $indicateurs);
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

    public function generateViewInformationsAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);
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

    public function modifierDateLieuAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getDateLieuForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-date-lieu', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            $this->getPropositionService()->initialisationDateRetour($proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form' => $form,
            'title' => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

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
                if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
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
        $membre = $this->getMembreService()->getRequestedMembre($this);

        if ($membre) {
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($membre->getProposition());
            $this->getMembreService()->delete($membre);
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
    }

    public function labelEuropeenAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getLabelEuropeenForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/label-europeen', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement d\'un label europeen',
            'form' => $form,
        ]);
        return $vm;
    }

    public function anglaisAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getAnglaisForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/anglais', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Renseignement de l\'utilisation de l\'anglais',
            'form' => $form,
        ]);
        return $vm;
    }

    public function confidentialiteAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/confidentialite', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
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

    public function changementTitreAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getChangementTitreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/changement-titre', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->update($request, $form, $proposition);
            if (!$this->isAllowed($these, PropositionPrivileges::PROPOSITION_MODIFIER_GESTION)) $this->getPropositionService()->annulerValidations($proposition);
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

                $proposition = $this->getPropositionService()->findByThese($these);
                $proposition->setEtat($this->getPropositionService()->getPropositionEtatByCode(Etat::ETABLISSEMENT));
                $this->getPropositionService()->update($proposition);
                break;
            default :
                throw new RuntimeException("Le role [" . $role->getCode() . "] ne peut pas valider cette proposition.");
        }

        return $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser-structure', ['these' => $these->getId()], [], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->getPropositionService()->annulerValidations($proposition);
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
        $proposition = $this->getPropositionService()->findByThese($these);

        $codirecteurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);


        $this->getEvenementService()->ajouterEvenement($proposition, Evenement::EVENEMENT_SIGNATURE);

        $exporter = new SiganturePresidentPdfExporter($this->renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'validations' => $this->getPropositionService()->getValidationSoutenance($these),
            'logos' => $this->getPropositionService()->getLogos($these),
            'libelle' => $this->getPropositionService()->generateLibelleSignaturePresidence($these),
            'nbCodirecteur' => count($codirecteurs),
        ]);
        $exporter->export('Document_pour_signature_-_'.$these->getId().'_-_'.str_replace(' ','_',$these->getDoctorant()->getIndividu()->getNomComplet()).'.pdf');
        exit;
    }

    public function toggleSursisAction()
    {
        $these = $this->requestedThese();
        $proposition = $this->getPropositionService()->findByThese($these);

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
    private function update(Request $request, Form $form, Proposition $proposition)
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
        $proposition = $this->getPropositionService()->findByThese($these);

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
        $soutenances = $this->getPropositionService()->getSoutenancesAutoriseesByEcoleDoctorale($ecole);

        return new ViewModel([
            'ecole' => $ecole,
            'soutenances' => $soutenances,
        ]);
    }
}
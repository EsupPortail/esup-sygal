<?php

namespace Soutenance\Controller\Proposition;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\Confidentialite\ConfidentialiteFormAwareTrait;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\DateLieu\DateLieuFormAwareTrait;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisAwareTrait;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisForm;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Membre\MembreFromAwareTrait;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Form\Refus\RefusFormAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PropositionController extends AbstractActionController {
    use MembreServiceAwareTrait;
    use NotifierSoutenanceServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use ValidatationServiceAwareTrait;

    use DateLieuFormAwareTrait;
    use MembreFromAwareTrait;
    use LabelEtAnglaisAwareTrait;
    use ConfidentialiteFormAwareTrait;
    use RefusFormAwareTrait;

    public function propositionAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        if (!$proposition) {
            $proposition = new Proposition();
            $proposition->setThese($these);
            $this->getPropositionService()->create($proposition);

            /** @var Acteur[] $encadrements */
            $encadrements = $these->getEncadrements();
            foreach ($encadrements as $encadrement) {
                $this->getMembreService()->createMembre($proposition, $encadrement);
            }
            $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();
        /** @var Role $currentRole */
        $currentRole = $this->userContextService->getSelectedIdentityRole();


        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'doctorant'         => $these->getDoctorant(),
            'directeurs'        =>$these->getEncadrements(false),
            'validations'       => $this->getPropositionService()->getValidationSoutenance($these),
            'validationActeur'  => $this->getPropositionService()->isValidated($these, $currentIndividu, $currentRole),
            'roleCode'          => $currentRole,
            'indicateurs'       => $this->getPropositionService()->computeIndicateur($proposition),
            'juryOk'            => $this->getPropositionService()->juryOk($proposition),
            'isOk'              => $this->getPropositionService()->isOk($proposition),
        ]);
    }

    public function modifierDateLieuAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var DateLieuForm $form */
        $form = $this->getDateLieuForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-date-lieu', ['these' => $idThese], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'form'              => $form,
            'title'             => 'Renseigner la date et le lieu de la soutenance',
        ]);
        return $vm;
    }

    public function modifierMembreAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var MembreForm $form */
        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/modifier-membre', ['these' => $these->getId()], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = null;
        if ($idMembre) $membre = $this->getMembreService()->find($idMembre);
        else           {
            $membre = new Membre();
            $membre->setProposition($proposition);
        }
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($idMembre)  {
                    $this->getMembreService()->update($membre);
                }
                else {
                    $this->getMembreService()->create($membre);
                }
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setVariables([
            'form'                  => $form,
            'title'                 => 'Renseigner les informations sur un membre du jury',
        ]);
        return $vm;
    }

    public function effacerMembreAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->getPropositionService()->annulerValidations($proposition);
        }
        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);
    }

    public function labelEtAnglaisAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var LabelEtAnglaisForm  $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(LabelEtAnglaisForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/label-et-anglais', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Renseignement d\'un label ou thèse en anglais',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function confidentialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getConfidentialiteForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/confidentialite', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->getPropositionService()->annulerValidations($proposition);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title'             => 'Renseignement des informations relatives à la confidentialité',
            'form'              => $form,
        ]);
        return $vm;
    }

    public function validerActeurAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierSoutenanceService()->triggerValidationProposition($these, $validation);

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();

        /** @var Acteur[] $acteurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray(), [ $doctorant ]);

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            if ($this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $acteur->getIndividu()) === null) {
                $allValidated = false;
                break;
            }
        }
        if ($allValidated) $this->getNotifierSoutenanceService()->triggerNotificationUniteRechercheProposition($these);

        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);

    }

    public function validerStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /**
         * @var Role $role
         * @var Individu $individu
         */
        $role =  $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        switch($role->getCode()) {
            case Role::CODE_UR :
                $this->getValidationService()->validateValidationUR($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationEcoleDoctoraleProposition($these);
                break;
            case Role::CODE_ED :
                $this->getValidationService()->validateValidationED($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationBureauDesDoctoratsProposition($these);
                break;
            case Role::CODE_BDD :
                $this->getValidationService()->validateValidationBDD($these, $individu);
                $this->getNotifierSoutenanceService()->triggerNotificationPropositionValidee($these);
                $this->getNotifierSoutenanceService()->triggerNotificationPresoutenance($these);
                break;
            default :
                throw new RuntimeException("Le role [".$role->getCode()."] ne peut pas valider cette proposition.");
                break;
        }

        $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var RefusForm $form */
        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser-structure', ['these' => $these->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->getPropositionService()->annulerValidations($proposition);
                $currentUser = $this->userContextService->getIdentityIndividu();
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                $this->getNotifierSoutenanceService()->triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $data['motif']);
            }
        }

        return new ViewModel([
            'title'             => "Motivation du refus de la proposition de soutenance",
            'form'              => $form,
            'these'             => $these,
        ]);
    }

    /** Document pour la signature en présidence */
    public function signaturePresidenceAction()
    {
        /**
         * @var These $these
         * @var Proposition $proposition
         */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $proposition = $this->getPropositionService()->findByThese($these);

        /* @var $renderer \Zend\View\Renderer\PhpRenderer */
        $renderer = $this->getServiceLocator()->get('view_renderer');

        $exporter = new SiganturePresidentPdfExporter($renderer, 'A4');
        $exporter->setVars([
            'proposition' => $proposition,
            'validations' => $this->getPropositionService()->getValidationSoutenance($these),
            'logos'       => $this->getPropositionService()->getLogos($these),
            'libelle'     => $this->getPropositionService()->generateLibelleSignaturePresidence($these),
            'nbCodirecteur'     => 1,
        ]);
        $exporter->export('export.pdf');
        exit;
    }

    public function avancementAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Acteur[] $directeurs */
        $directeurs = $these->getEncadrements(false);

        /** @var Membre[] $rapporteurs */
        $rapporteurs = ($proposition)?$proposition->getRapporteurs():[];

        return new ViewModel([
            'these'             => $these,
            'proposition'       => $proposition,
            'jury'              => $this->getPropositionService()->juryOk($proposition),
            'validations'       => ($proposition)?$this->getPropositionService()->getValidationSoutenance($these):[],
            'directeurs'        => $directeurs,
            'rapporteurs'       => $rapporteurs,
        ]);
    }
}
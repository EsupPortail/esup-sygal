<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisForm;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Form\SoutenanceRefus\SoutenanceRefusForm;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\SignaturePresident\SiganturePresidentPdfExporter;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 *
 * Controlleur principale du module de gestion de la soutenance
 * @method isAllowed($these, $PRIVILEGE)
 */

class SoutenanceController extends AbstractActionController {
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use AvisServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    public function indexAction()
    {
        $these       = null;
        $proposition = null;
        $directeurs  = null;
        $rapporteurs = null;
        $validations = null;
        $avis        = null;

        $theseId = $this->params()->fromRoute('these');
        if ($theseId) {
            /** @var These $these */
            $these = $this->getTheseService()->getRepository()->find($theseId);
            $proposition = $this->getPropositionService()->findByThese($these);

            /** @var Acteur[] $directeurs */
            $directeurs = $these->getEncadrements(false);


            if ($proposition) $rapporteurs = $proposition->getRapporteurs();

            /** TODO récupérer les validations via le service */
            $validations = [];
            $validations[$these->getDoctorant()->getIndividu()->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $these->getDoctorant()->getIndividu());
            foreach ($directeurs as $directeur) {
                $validations[$directeur->getIndividu()->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $directeur->getIndividu());
            }

            $validationUR = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
            if ($validationUR) $validations["unite-recherche"] = current($validationUR);
            $validationED = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
            if ($validationED) $validations["ecole-doctorale"] = current($validationED);
            $validationBDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
            if ($validationBDD) $validations["bureau-doctorat"] = current($validationBDD);

            $avis = [];
            if ($proposition !== null) {
                foreach ($rapporteurs as $rapporteur) {
                    $validationR = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $rapporteur->getIndividu());
                    if ($validationR) $validations[$rapporteur->getIndividu()->getId()] = $validationR;

                    $avisRapporteur = $this->getAvisService()->getAvisByRapporteur($rapporteur, $these);
                    if ($avisRapporteur) $avis[$rapporteur->getIndividu()->getId()] = $avisRapporteur->getAvis();
                }
            }
        }


        /** @var These[] $theses */
        $theses = [];
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($individu !== null) {
            $theses = $this->getTheseService()->getRepository()->fetchThesesByEncadrant($individu);
        }
        $doctorant = $this->userContextService->getIdentityDoctorant();
        if ($doctorant !== null) {
            $theses = $this->getTheseService()->getRepository()->fetchThesesByDoctorant($doctorant);
        }

        if ($theses === []) {
            $theses[] = $this->getTheseService()->getRepository()->find(41321);
        }

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'directeurs' => $directeurs,
            'rapporteurs' => $rapporteurs,
            'validations' => $validations,
            'avis'  => $avis,

            'theses' => $theses,
            'role' => $role,
        ]);
    }

    public function propositionAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à visualiser cette propositions de soutenance.");
        }

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

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();
        /** @var Individu[] $directeurs */
        $directeurs = $these->getEncadrements(true);

        $validations = [];
        $validations[$doctorant->getIndividu()->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $doctorant->getIndividu());
        foreach ($directeurs as $directeur) {
            $validations[$directeur->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $directeur);
        }

        $validationUR = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
        if ($validationUR) $validations["unite-recherche"] = current($validationUR);
        $validationED = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
        if ($validationED) $validations["ecole-doctorale"] = current($validationED);
        $validationBDD = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
        if ($validationBDD) $validations["bureau-doctorat"] = current($validationBDD);

        return new ViewModel([
                'these' => $these,
                'proposition' => $proposition,
                'doctorant' => $doctorant,
                'directeurs' => $directeurs = $these->getEncadrements(false),
                'validations' => $validations,
                'currentIndividu' => $currentIndividu,
                'roleCode' => $this->userContextService->getSelectedIdentityRole()->getCode(),
                'individuId' => $this->userContextService->getIdentityDb()->getIndividu()->getId(),
                'indicateurs' => $this->getPropositionService()->computeIndicateur($proposition),
                'juryOk' => $this->getPropositionService()->juryOk($proposition),
                'isOk'   => $this->getPropositionService()->isOk($proposition),
            ]
        );
    }

    /**
     * Modification de la date et lieu de la soutenance
     * => se fait dans une fenêtre modale
     */
    public function modifierDateLieuAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier cette propositions de soutenance.");
        }

        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceDateLieuForm::class);
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

        return new ViewModel([
                'form' => $form,
                'title' => 'Renseigner la date et le lieu de la soutenance',
            ]
        );
    }

    public function modifierMembreAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier cette propositions de soutenance.");
        }

        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceMembreForm::class);
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

               return new ViewModel([
                'form' => $form,
                'title' => 'Renseigner les informations sur un membre du jury',
            ]
        );
    }

    public function effacerMembreAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier cette propositions de soutenance.");
        }

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->getPropositionService()->annulerValidations($proposition);
        }
        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);
    }

    public function confidentialiteAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var ConfidentialiteForm  $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(ConfidentialiteForm::class);
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

        return new ViewModel([
            'title' => 'Renseignement des informations relatives à la confidentialité',
            'form' => $form,
        ]);
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

        return new ViewModel([
            'title' => 'Renseignement d\'un label ou thèse en anglais',
            'form' => $form,
        ]);
    }

    public function validerAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ACTEUR);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier cette propositions de soutenance.");
        }

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierService()->triggerValidationProposition($these, $validation);

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();

        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        /** @var Acteur[] $acteurs */
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $acteurs[] = $doctorant;
        $allValidated = true;
        foreach ($acteurs as $acteur) {
            if ($this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $acteur->getIndividu()) === null) {
                $allValidated = false;
                break;
            }
        }

        if ($allValidated) $this->getNotifierService()->triggerNotificationUniteRechercheProposition($these);



        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);

    }

    public function validerStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

//        /** @var Proposition $proposition */
//        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var string $role */
        $role =  $this->userContextService->getSelectedIdentityRole()->getCode();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        if ($role=== Role::CODE_UR) {
            $this->getValidationService()->validateValidationUR($these, $individu);
            $this->getNotifierService()->triggerNotificationEcoleDoctoraleProposition($these);
        }
        if ($role=== Role::CODE_ED) {
            $this->getValidationService()->validateValidationED($these, $individu);
            $this->getNotifierService()->triggerNotificationBureauDesDoctoratsProposition($these);
        }
        if ($role=== Role::CODE_BDD) {
            $this->getValidationService()->validateValidationBDD($these, $individu);
            $this->getNotifierService()->triggerNotificationPropositionValidee($these);
            $this->getNotifierService()->triggerNotificationPresoutenance($these);
        }

        $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var SoutenanceRefusForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceRefusForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser-structure', ['these' => $these->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->getPropositionService()->annulerValidations($proposition);
                $currentUser = $this->userContextService->getIdentityIndividu();
                $currentRole = $this->userContextService->getSelectedIdentityRole();
                $this->getNotifierService()->triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $data['motif']);
            }
        }

        return new ViewModel([
                'title' => "Motivation du refus de la proposition de soutenance",
                'form' => $form,
                'these' => $these,
            ]
        );
    }

//    public function addActeursAction()
//    {
//        $this->getActeurService()->addActeur41321();
//        $this->redirect()->toRoute('soutenance', [], [], true);
//    }
//
//    public function removeActeursAction()
//    {
//        $this->getActeurService()->removeActeur41321();
//        $this->redirect()->toRoute('soutenance', [], [], true);
//    }
//
//    public function restoreValidationAction()
//    {
//        $this->getActeurService()->restaureValidation();
//        $this->redirect()->toRoute('soutenance', [], [], true);
//    }

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
}


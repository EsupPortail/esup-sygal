<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Form\SoutenanceRefus\SoutenanceRefusForm;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
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
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();
        /** @var Individu[] $directeurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $directeurs = [];
        /** @var Acteur $acteur */
        foreach ($acteurs as $acteur) $directeurs[] = $acteur->getIndividu();

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
                'directeurs' => $directeurs,
                'validations' => $validations,
                'currentIndividu' => $currentIndividu,
                'roleCode' => $this->userContextService->getSelectedIdentityRole()->getCode(),
                'individuId' => $this->userContextService->getIdentityDb()->getIndividu()->getId(),
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
                $this->unvalidate($these);
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
                $this->unvalidate($these);
            }
        }

               return new ViewModel([
                'form' => $form,
                'title' => 'Renseigner les informations sur un membre du jury',
            ]
        );
    }

    public function effacerMembreAction() {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier cette propositions de soutenance.");
        }

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->unvalidate($these);
        }
        $this->redirect()->toRoute('soutenance/proposition',['these' => $idThese],[],true);
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

    /**
     * @param These $these
     */
    public function unvalidate($these) {
        /** @var Validation[] $validations */
        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
            $this->getNotifierService()->triggerDevalidationProposition($validation);
        }
    }

    public function validerStructureAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var string $role */
        $role =  $this->userContextService->getSelectedIdentityRole()->getCode();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        if ($role=== Role::CODE_UR) {
            $this->getValidationService()->validateValidationUR($these, $individu);
            //TODO NOTIFIER ED
        }
        if ($role=== Role::CODE_ED) {
            $this->getValidationService()->validateValidationED($these, $individu);
            //TODO NOTIFIER BDD
        }
        if ($role=== Role::CODE_BDD) {
            $this->getValidationService()->validateValidationBDD($these, $individu);
            //TODO NOTIFIER ACTEURS + ED + UR
            // go to presoutenance
        }

        $this->redirect()->toRoute('soutenance/proposition', ['these' => $these->getId()], [], true);

    }

    public function refuserAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var SoutenanceRefusForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceRefusForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/proposition/refuser', ['these' => $these->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->unvalidate($these);
                $currentUser = $this->userContextService->getIdentityIndividu();
                $this->getNotifierService()->triggerRefusPropositionSoutenance($these, $currentUser, $data['motif']);
//                $this->redirect()->toRoute('soutenance/proposition',['these' => $these->getId()],[],true);
            }
        }

        return new ViewModel([
                'form' => $form,
                'these' => $these,
            ]
        );
    }
}


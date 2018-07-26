<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SoutenanceController extends AbstractActionController {
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use UserContextServiceAwareTrait;


    public function indexAction()
    {
        $propositions = $this->getPropositionService()->findAll();
        return new ViewModel([
            'propositions' => $propositions,
            ]
        );
    }

    public function modifierDateLieuAction() {

        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceDateLieuForm::class);

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
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
                $this->redirect()->toRoute('soutenance/constituer',['these' => $these->getId()],[],true);
            }
        }

        return new ViewModel([
                'form' => $form,
            ]
        );
    }

    public function modifierMembreAction() {
        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceMembreForm::class);

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
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
                $this->redirect()->toRoute('soutenance/constituer',['these' => $these->getId()],[],true);
            }
        }

               return new ViewModel([
                'form' => $form,
            ]
        );
    }

    public function effacerMembreAction() {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->unvalidate($these);
        }
        $this->redirect()->toRoute('soutenance/constituer',['these' => $idThese],[],true);
    }

    //TODO utiliser la proposition et recup la these via ->getThese() ?
    //TODO creer si aucune proposition existe
    public function constituerAction()
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
        return new ViewModel([
                'these' => $these,
                'proposition' => $proposition,
                'doctorant' => $doctorant,
                'directeurs' => $directeurs,
                'validations' => $validations,
                'currentIndividu' => $currentIndividu,
            ]
        );
    }

    public function validerAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierService()->triggerValidationProposition($these, $validation);
        //TODO notifier Dir CoDir(s) Doct

        //TODO si tout le monde à valider : 1) bloquer les modifications 2) Notifier ED

        $this->redirect()->toRoute('soutenance/constituer',['these' => $idThese],[],true);

    }

    public function refuserAction() {

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
}


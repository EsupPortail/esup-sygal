<?php

namespace Soutenance\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
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


    public function indexAction()
    {
        $propositions = $this->getPropositionService()->findAll();
        return new ViewModel([
            'propositions' => $propositions,
            ]
        );
    }

    //TODO attention au format de la date ==> utiliser datepicker et timepicker ...
    //TODO ajouter la creation d'une nouvelle proposition
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
                if ($idMembre)  $this->getMembreService()->update($membre);
                else            $this->getMembreService()->create($membre);
                $this->redirect()->toRoute('soutenance/constituer',['these' => $these->getId()],[],true);
            }
        }

               return new ViewModel([
                'form' => $form,
            ]
        );
    }

    public function effacerMembreAction() {

        $idThese = $this->params()->fromRoute('these');

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        $this->getMembreService()->delete($membre);
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

        return new ViewModel([
                'these' => $these,
                'proposition' => $proposition,
            ]
        );
    }

}


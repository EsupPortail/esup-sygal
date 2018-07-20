<?php

namespace Soutenance\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Proposition;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Service\PropositionServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SoutenanceController extends AbstractActionController {
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;


    public function indexAction()
    {
        $propositions = $this->getPropositionService()->findAll();
        return new ViewModel([
            'propositions' => $propositions,
            ]
        );
    }

    //TODO attention au format de la date ==> utiliser datepicker et timepicker ...
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

        return new ViewModel([
                'form' => $form,
            ]
        );
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


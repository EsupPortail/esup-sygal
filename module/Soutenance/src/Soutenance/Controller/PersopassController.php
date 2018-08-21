<?php

namespace Soutenance\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\PersopassModifier\PersopassModifierForm;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PersopassController extends AbstractActionController
{
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use MembreServiceAwareTrait;

    public function afficherAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute("these");
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre[] $membres */
        $membres = $proposition->getMembres();

        return new ViewModel([
            'these' => $these,
            'membres' => $membres,
        ]);
    }

    public function modifierAction() {

        /** @var These $these */
        $idThese = $this->params()->fromRoute("these");
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var PersopassModifierForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(PersopassModifierForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/persopass/modifier', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $membre->setPersopass($data['persopass']);
            $membre->setNouveau($data['nouveau']);
            $this->getMembreService()->update($membre);
//            $this->redirect()->toRoute('soutenance/persopass',['these' => $these->getId()],[],true);
        }

        return new ViewModel([
            'these' => $these,
            'membre' => $membre,
            'form' => $form,
        ]);
    }

    public function notifierAction() {

        return new ViewModel([]);
    }
}


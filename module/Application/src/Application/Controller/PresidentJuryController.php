<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Form\AdresseMail\AdresseMailFormAwareTrait;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use DateInterval;
use DateTime;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Model\ViewModel;

/** @method FlashMessenger flashMessenger() **/

class PresidentJuryController extends AbstractActionController {
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use TheseServiceAwareTrait;
    use AdresseMailFormAwareTrait;

    public function indexAction() {
        $date = (new DateTime())->sub(new DateInterval('P4M'));

        $presidents = $this->getActeurService()->getRepository()->fetchPresidentDuJuryTheseAvecCorrection();
        $presidents = array_filter($presidents, function (Acteur $president) use ($date) { return $president->getThese()->getDateSoutenance() > $date;});

        return new ViewModel([
            'presidents' => $presidents,
        ]);
    }

    public function notifierCorrectionAction()
    {
        $president = $this->getActeurService()->getRequestedActeur($this, 'president');
        $these = $president->getThese();

        $message = $this->getTheseService()->notifierCorrectionsApportees($these);
        if ($message[0] === 'success') $this->flashMessenger()->addSuccessMessage($message[1]);
        if ($message[0] === 'error')   $this->flashMessenger()->addErrorMessage($message[1]);


        return $this->redirect()->toRoute('president-jury', [], [], true);
    }

    public function ajouterMailAction()
    {
        $president = $this->getActeurService()->getRequestedActeur($this, 'president');

        $membre = $this->getMembreService()->createDummyMembre();
        $form = $this->getAdresseMailForm();
        $form->setAttribute('action', $this->url()->fromRoute('president-jury/ajouter-mail', ['president' => $president->getId()], [], true));
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $membre->setActeur($president);
                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un mail pour le président du jury",
            'form' => $form,
        ]);
        $vm->setTemplate("soutenance/default/default-form");
        return $vm;
    }

    public function supprimerMailAction()
    {
        $president = $this->getActeurService()->getRequestedActeur($this, 'president');
        $membre = $this->getMembreService()->getMembreByActeur($president);
        $this->getMembreService()->delete($membre);
        return $this->redirect()->toRoute('president-jury', [], [], true);
    }
}
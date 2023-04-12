<?php

namespace These\Controller;

use Application\Entity\Db\Utilisateur;
use Application\Form\AdresseMail\AdresseMailFormAwareTrait;
use DateInterval;
use DateTime;
use Depot\Service\These\DepotServiceAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;

/** @method FlashMessenger flashMessenger() **/

class PresidentJuryController extends AbstractActionController
{
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use TheseServiceAwareTrait;
    use AdresseMailFormAwareTrait;
    use DepotServiceAwareTrait;

    public function indexAction() {
        $date = (new DateTime())->sub(new DateInterval('P4M'));

        $presidents = $this->getActeurService()->getRepository()->findActeursPresidentDuJuryForThesesAvecCorrection();
        $presidents = array_filter($presidents, function (Acteur $president) use ($date) { return $president->getThese()->getDateSoutenance() > $date;});

        return new ViewModel([
            'presidents' => $presidents,
        ]);
    }

    /**
     * @todo : déplacer dans la module Depot.
     */
    public function notifierCorrectionAction()
    {
        $president = $this->getActeurService()->getRequestedActeur($this, 'president');
        $these = $president->getThese();

        $resultArray = $this->depotService->notifierCorrectionsApportees($these);

        if ($resultArray[0] === 'success') $this->flashMessenger()->addSuccessMessage($resultArray[1]);
        if ($resultArray[0] === 'error')   $this->flashMessenger()->addErrorMessage($resultArray[1]);

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
<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use DateTime;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Form\Seance\SeanceFormAwareTrait;
use Formation\Service\Seance\SeanceServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class SeanceController extends AbstractController
{
    use EntityManagerAwareTrait;
    use SeanceServiceAwareTrait;
    use SeanceFormAwareTrait;

    public function indexAction()
    {
        /** @var Seance[] $seances */
        $seances = $this->getEntityManager()->getRepository(Seance::class)->findAll();

        return new ViewModel([
            'seances' => $seances,
        ]);
    }

    public function ajouterAction()
    {
        /** @var Session|null $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);

        if ($session === null) {
            throw new RuntimeException("Aucune session pour ajouter la séance");
        }

        $seance = new Seance();
        $seance->setSession($session);

        $form = $this->getSeanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/seance/ajouter', ['session' => $session->getId()], [], true));
        $form->bind($seance);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSeanceService()->create($seance);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une séance pour la session de formation #".$session->getId(),
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierAction()
    {
        /** @var Seance|null $seance */
        $seance = $this->getEntityManager()->getRepository(Seance::class)->getRequestedSeance($this);

        $form = $this->getSeanceForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/seance/modifier', ['seance' => $seance->getId()], [], true));
        $form->bind($seance);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSeanceService()->update($seance);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une séance pour la seance de formation #".$seance->getId(),
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserAction()
    {
        /** @var Seance|null $seance */
        $seance = $this->getEntityManager()->getRepository(Seance::class)->getRequestedSeance($this);

        $this->getSeanceService()->historise($seance);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/seance');

    }

    public function restaurerAction()
    {
        /** @var Seance|null $seance */
        $seance = $this->getEntityManager()->getRepository(Seance::class)->getRequestedSeance($this);

        $this->getSeanceService()->restore($seance);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/seance');
    }

    public function supprimerAction()
    {
        /** @var Seance|null $seance */
        $seance = $this->getEntityManager()->getRepository(Seance::class)->getRequestedSeance($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSeanceService()->delete($seance);
            exit();
        }

        $vm = new ViewModel();
        if ($seance !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la seance #" . $seance->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/seance/supprimer', ["seance" => $seance->getId()], [], true),
            ]);
        }
        return $vm;
    }
}
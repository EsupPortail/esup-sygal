<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Individu;
use Formation\Entity\Db\Formation;
use Formation\Form\Formation\FormationFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class FormationController extends AbstractController 
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use FormationFormAwareTrait;

    public function indexAction()
    {
        /** @var Formation[] $formations */
        $formations = $this->getEntityManager()->getRepository(Formation::class)->findAll();

        return new ViewModel([
            'formations' => $formations,
        ]);
    }

    public function ajouterAction()
    {
        $formation = new Formation();

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter', [], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->create($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un module de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function modifierAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier', [], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->update($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un module de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function historiserAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $this->getFormationService()->historise($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/formation');

    }

    public function restaurerAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $this->getFormationService()->restore($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) {
            return $this->redirect()->toUrl($retour);
        }
        return $this->redirect()->toRoute('formation/formation');
    }

    public function supprimerAction()
    {
        /** @var Formation|null $formation */
        $formation = $this->getEntityManager()->getRepository(Formation::class)->getRequestedFormation($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormationService()->delete($formation);
            exit();
        }

        $vm = new ViewModel();
        if ($formation !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la formation #" . $formation->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer', ["formation" => $formation->getId()], [], true),
            ]);
        }
        return $vm;
    }
}
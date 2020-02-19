<?php

namespace ComiteSuivi\Controller;


use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviFormAwareTrait;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ComiteSuiviController extends AbstractActionController {
    use ComiteSuiviServiceAwareTrait;
    use TheseServiceAwareTrait;
    use DateTimeTrait;

    use ComiteSuiviFormAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $comites = [];
        if ($these !== null) {
            $comites = $this->getComiteSuiviService()->getComitesSuivisByThese($these);
        }

        return new ViewModel([
            'these' => $these,
            'comites' => $comites,
        ]);
    }

    public function ajouterAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        /** @var ComiteSuivi[] $comites */
        $comites = $this->getComiteSuiviService()->getComitesSuivisByThese($these);
        $max = 1;
        foreach ($comites as $comite) {
            if ($comite->getAnneeThese() > $max) $max = $comite->getAnneeThese();
        }

        $comite = new ComiteSuivi();
        $comite->setThese($these);
        $comite->setAnneeScolaire($this->getAnneeScolaire());
        $comite->setAnneeThese($max+1);

        $form = $this->getComiteSuiviForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/ajouter', [], [], true));
        $form->bind($comite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getComiteSuiviService()->create($comite);
            }
        }

        $vm =  new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setTerminal(true);
        $vm->setVariables([
            'title' => "Ajout d'un nouveau comité de suivi de thèse",
            'form' => $form,
        ]);
        return $vm;
    }

    public function restaurerAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $this->getComiteSuiviService()->restore($comite);
        return $this->redirect()->toRoute('comite-suivi', ['these' => $comite->getThese()->getId()], [], true);
    }

    public function historiserAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $this->getComiteSuiviService()->historise($comite);
        return $this->redirect()->toRoute('comite-suivi', ['these' => $comite->getThese()->getId()], [], true);
    }

    public function supprimerAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getComiteSuiviService()->delete($comite);
            exit();
        }

        $vm = new ViewModel();
        if ($comite !== null) {
            $vm->setTemplate('comite-suivi/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de l'instance du comité de suivi de thèse",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('comite-suivi/supprimer', ["comite-suivi" => $comite->getId()], [], true),
            ]);
        }
        return $vm;
    }

}
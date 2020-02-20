<?php

namespace ComiteSuivi\Controller;


use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Entity\Db\CompteRendu;
use ComiteSuivi\Entity\Db\Membre;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviFormAwareTrait;
use ComiteSuivi\Form\CompteRendu\CompteRenduFormAwareTrait;
use ComiteSuivi\Form\Membre\MembreFormAwareTrait;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use ComiteSuivi\Service\CompteRendu\CompteRenduServiceAwareTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use ComiteSuivi\View\Helper\AnneeTheseViewHelper;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ComiteSuiviController extends AbstractActionController {
    use DateTimeTrait;

    use ComiteSuiviServiceAwareTrait;
    use CompteRenduServiceAwareTrait;
    use MembreServiceAwareTrait;
    use TheseServiceAwareTrait;

    use ComiteSuiviFormAwareTrait;
    use CompteRenduFormAwareTrait;
    use MembreFormAwareTrait;

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
                exit();
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

    public function afficherAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $anneeViewHelper = new AnneeTheseViewHelper();
        $anneeThese = strtolower($anneeViewHelper->render($comite->getAnneeThese()));

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/comite-suivi/instance');
        $vm->setVariables([
            'title' => "Comité de suivi de thèse pour la " . $anneeThese . " de la thèse de " .$comite->getThese()->getDoctorant()->getIndividu()->getNomComplet(),
            'these' => $comite->getThese(),
            'comite' => $comite,
            'action' => 'afficher',
        ]);
        return $vm;
    }

    public function modifierAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/comite-suivi/instance');
        $vm->setVariables([
            'these' => $comite->getThese(),
            'comite' => $comite,
            'action' => 'modifier',
        ]);
        return $vm;
    }

    public function modifierInfosAction()
    {
        /** @var ComiteSuivi $comite */
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $form = $this->getComiteSuiviForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/modifier-infos', ['these' => $comite->getThese()->getId(), 'comite-suivi' => $comite->getId()], [], true));
        $form->bind($comite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getComiteSuiviService()->update($comite);
                exit();
            }
        }

        $vm =  new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setTerminal(true);
        $vm->setVariables([
            'title' => "Modifier les informations du comité de suivi de thèse",
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

    /** PARTIE MEMBRE *************************************************************************************************/

    public function ajouterMembreAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $membre = new Membre();
        $membre->setComite($comite);

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/ajouter-membre', ['comite-suivi' => $comite->getId()], [], true));
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setVariables([
            'title' => "Ajout d'un membre au comité de suivi",
            'form' => $form,
        ]);
        return $vm;
    }

    public function modifierMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/modifier-membre', ['membre' => $membre->getId()], [], true));
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMembreService()->update($membre);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setVariables([
            'title' => "Modification d'un membre au comité de suivi",
            'form' => $form,
        ]);
        return $vm;
    }

    public function historiserMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->historise($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }

    public function restaurerMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->restore($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }

    public function supprimerMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->delete($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }



}
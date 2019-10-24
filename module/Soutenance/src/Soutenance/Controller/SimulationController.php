<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Source;
use Application\Entity\Db\These;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Form\ActeurSimule\ActeurSimuleFormAwareTrait;
use Soutenance\Service\Simulation\SimulationService;
use Soutenance\Service\Simulation\SimulationServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SimulationController extends AbstractActionController {
    use SimulationServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ActeurSimuleFormAwareTrait;
    use SourceServiceAwareTrait;

    public function indexAction() {
        $these      = null;
        $theseId    = $this->params()->fromQuery('these');
        if ($theseId != null) $these = $this->getTheseService()->getRepository()->find($theseId);
        $acteurs  = $this->getSimulationService()->getActeursSimules($these);

        return new ViewModel([
            'these' => $these,
            'acteurs' => $acteurs,
        ]);

    }

    public function ajouterActeurSimuleAction() {
        /** @var These $these */
        $these      = null;
        $query = [];

        $theseId    = $this->params()->fromQuery('these');
        if ($theseId != null) {
            $these = $this->getTheseService()->getRepository()->find($theseId);
            $query = ["query" => ["these" => $theseId]];
        }

        $acteur = new Acteur();
        $individu = new Individu();
        $acteur->setIndividu($individu);

        $form = $this->getActeurSimuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('simulation/ajouter-acteur-simule', [], $query, true));
        $form->bind($acteur);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var Source $source */
                $source = $this->sourceService->getRepository()->find(SimulationService::SIMULATION_SOURCE);
                $individu->setSourceCode(uniqid());
                $acteur->setSourceCode(uniqid());
                $acteur->setThese($these);
                $individu->setSource($source);
                $acteur->setSource($source);
                $this->getSimulationService()->create($acteur);
                return $this->redirect()->toRoute('simulation', [], $query, true);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => "Ajout d'un acteur simulé",
            'form' => $form,
        ]);
        return $vm;

    }

    public function modifierActeurSimuleAction() {
        $these      = null;
        $query = [];

        $theseId    = $this->params()->fromQuery('these');
        if ($theseId != null) {
            $these = $this->getTheseService()->getRepository()->find($theseId);
            $query = ["query" => ["these" => $theseId]];
        }

        $acteur = $this->getSimulationService()->getRequestedActeurSimule($this);
        $form = $this->getActeurSimuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('simulation/modifier-acteur-simule', ['acteur' => $acteur->getId()], $query, true));
        $form->bind($acteur);


        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSimulationService()->update($acteur);
                return $this->redirect()->toRoute('simulation', [], $query, true);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => "Modification d'un acteur simulé",
            'form' => $form,
        ]);
        return $vm;
    }

    public function supprimerActeurSimuleAction() {
        $these      = null;
        $query = [];

        $theseId    = $this->params()->fromQuery('these');
        if ($theseId != null) {
            $these = $this->getTheseService()->getRepository()->find($theseId);
            $query = ["query" => ["these" => $theseId]];
        }

        $acteur = $this->getSimulationService()->getRequestedActeurSimule($this);
        $this->getSimulationService()->delete($acteur);
        return $this->redirect()->toRoute('simulation', [], $query, true);
    }

}
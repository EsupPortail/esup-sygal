<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Source;
use Application\Entity\Db\These;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Form\ActeurSimule\ActeurSimuleFormAwareTrait;
use Soutenance\Service\IndividuSimulable\IndividuSimulableServiceAwareTrait;
use Soutenance\Service\Simulation\SimulationService;
use Soutenance\Service\Simulation\SimulationServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SimulationController extends AbstractActionController {
    use SimulationServiceAwareTrait;
    use IndividuSimulableServiceAwareTrait;
    use TheseServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use SourceServiceAwareTrait;
    use ActeurSimuleFormAwareTrait;

    public function indexAction() {
        /** @var These $these */
        $theseId    = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Acteur[] $acteurs */
        $acteurs  = $this->getSimulationService()->getActeursSimules($these);

        return new ViewModel([
            'these' => $these,
            'acteurs' => $acteurs,
        ]);

    }

    public function ajouterActeurSimuleAction() {
        /** @var These $these */
        $theseId    = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Acteur $acteur */
        $acteur = new Acteur();

        $form = $this->getActeurSimuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('simulation/ajouter-acteur-simule', ['these' => $these->getId()], [], true));
        $form->bind($acteur);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var Source $source */
                $source = $this->sourceService->getRepository()->find(SimulationService::SIMULATION_SOURCE);
                $acteur->setSourceCode(uniqid());
                $acteur->setThese($these);
                $acteur->setSource($source);
                $this->getSimulationService()->create($acteur);
                return $this->redirect()->toRoute('simulation', ['these' => $these->getId()], [], true);
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
        /** @var These $these */
        $theseId    = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Acteur $acteur */
        $acteur = $this->getSimulationService()->getRequestedActeurSimule($this);
        $form = $this->getActeurSimuleForm();
        $form->setAttribute('action', $this->url()->fromRoute('simulation/modifier-acteur-simule', ['these' => $these->getId(), 'acteur' => $acteur->getId()], [], true));
        $form->bind($acteur);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSimulationService()->update($acteur);
                return $this->redirect()->toRoute('simulation', ['these' => $these->getId()], [], true);
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
        /** @var These $these */
        $theseId    = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        /** @var Acteur $acteur */
        $acteur = $this->getSimulationService()->getRequestedActeurSimule($this);

        $this->getSimulationService()->delete($acteur);
        return $this->redirect()->toRoute('simulation', ['these' => $these->getId()], [], true);
    }
}
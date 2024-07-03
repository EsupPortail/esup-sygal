<?php

namespace Information\Controller;

use Information\Entity\Db\Information;
use Information\Form\InformationForm;
use Information\Service\InformationServiceAwareTrait;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class InformationController extends AbstractActionController {
    use InformationServiceAwareTrait;

    /**
     * @var InformationForm
     */
    private $informationForm;

    /**
     * @param InformationForm $informationForm
     */
    public function setInformationForm(InformationForm $informationForm): void
    {
        $this->informationForm = $informationForm;
    }

    public function indexAction()
    {
        $informations = $this->getInformationService()->getInformations();
        return new ViewModel([
            'informations' => $informations,
        ]);
    }

    public function afficherAction()
    {
        $informationId  = $this->params()->fromRoute('id');
        $information    = $this->getInformationService()->getInformation($informationId);

        return new ViewModel([
            'information' => $information,
        ]);
    }

    public function ajouterAction(): ViewModel
    {
        $information = $this->informationService->new();

        $form = $this->informationForm;
        $form->bind($information);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getInformationService()->create($information);
                $this->redirect()->toRoute('informations', [], [], true);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function modifierAction()
    {
        $informationId  = $this->params()->fromRoute('id');
        $information    = $this->getInformationService()->getInformation($informationId);

        /** @var InformationForm $form */
        $form = $this->informationForm;
        $form->bind($information);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getInformationService()->update($information);
                $this->redirect()->toRoute('informations', [], [], true);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function supprimerAction()
    {
        $informationId  = $this->params()->fromRoute('id');
        $information    = $this->getInformationService()->getInformation($informationId);

        $this->getInformationService()->delete($information);

        $this->redirect()->toRoute('informations', [], [], true);
    }
}

<?php

namespace UnicaenAvis\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenAvis\Form\AvisTypeForm;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AvisTypeController extends AbstractActionController
{
    use AvisServiceAwareTrait;

    private AvisTypeForm $avisTypeForm;

    public function setAvisTypeForm(AvisTypeForm $avisTypeForm): void
    {
        $this->avisTypeForm = $avisTypeForm;
    }

    public function indexAction(): array
    {
        return [
            'avisTypes' => $this->avisService->findAllAvisTypes(),
        ];
    }

    public function modifierAction(): array
    {
        $id = $this->params()->fromRoute('avisType');
        $avisType = $this->avisService->findOneAvisTypeById($id);

        $this->avisTypeForm->bind($avisType);

        if ($data = $this->params()->fromPost()) {
            $this->avisTypeForm->setData($data);
            if ($this->avisTypeForm->isValid()) {
                $this->avisService->saveAvisType($avisType);
                var_dump('yes!');
                $this->flashMessenger()->addSuccessMessage("Bravo !");
            }
        }

        return [
            'form' => $this->avisTypeForm,
        ];
    }
}
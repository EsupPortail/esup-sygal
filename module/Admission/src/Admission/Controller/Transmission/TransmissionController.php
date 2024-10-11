<?php

namespace Admission\Controller\Transmission;

use Admission\Entity\Db\Transmission;
use Admission\Form\Transmission\TransmissionFormAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Transmission\TransmissionServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;

/**
 * Class TransmissionController
 *
 * @method FlashMessenger flashMessenger()
 */
class TransmissionController extends AbstractActionController
{
    use AdmissionServiceAwareTrait;
    use TransmissionServiceAwareTrait;
    use TransmissionFormAwareTrait;

    public function ajouterTransmissionAction(): Response|ViewModel
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        $transmissionInBdd = $this->transmissionService->getRepository()->findOneBy(["admission" => $admission]);
        $transmission = $transmissionInBdd ?: new Transmission();
        if($transmission->getId() === null) $transmission->setAdmission($admission);
        $form = $this->transmissionForm;
        $form->bind($transmission);
        $form->setAttribute('action', $this->url()->fromRoute('admission/ajouter-transmission',['admission' => $admission->getId()],[], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var Transmission $transmission */
                $transmission = $form->getData();
                $transmission->setAdmission($admission);

                if ($transmission->getId() === null) {
                    $this->transmissionService->create($transmission);
                }else{
                    $this->transmissionService->update($transmission);
                }
                $this->flashMessenger()->addSuccessMessage("Données concernant l'export vers Pégase enregistrées avec succès.");
            }
        }
        return (new ViewModel([
            'form' => $form,
            'title' => "Informations nécessaires pour l'export vers Pégase"
        ]))->setTemplate('admission/admission/transmission/modifier');
    }
}
<?php

namespace Admission\Controller\Validation;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Application\Controller\AbstractController;
use Laminas\Http\Response;

class AdmissionValidationController extends AbstractController
{
    use AdmissionServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use TypeValidationServiceAwareTrait;

    public function validerAction(): Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        /** @var TypeValidation $typeValidation */
        $typeValidationId = $this->params('typeValidation');
        $typeValidation = $this->typeValidationService->getRepository()->findTypeValidationById($typeValidationId);

        $redirectUrl = $this->params()->fromQuery('redirect');

        $admissionValidation = $this->admissionValidationService->newAdmissionValidation($admission, $typeValidation);
        $this->admissionValidationService->saveNewAdmissionValidation($admissionValidation);
        $event = $this->admissionValidationService->triggerEventValidationAjoutee($admissionValidation);

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        $individu = $admission->getIndividu()->getId();

        return $this->redirect()->toRoute('admission/document', ['individu' => $individu]);
    }

    public function devaliderAction(): Response
    {
        $admissionValidationId = $this->params()('admissionValidation');
        /** @var AdmissionValidation $admissionValidation */
        $admissionValidation = $this->admissionValidationService->getRepository()->find($admissionValidationId);
        $redirectUrl = $this->params()->fromQuery('redirect');

        $this->admissionValidationService->deleteAdmissionValidation($admissionValidation);
        $event = $this->admissionValidationService->triggerEventValidationSupprimee($admissionValidation);

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "%s supprimée avec succès.",
            $admissionValidation->getTypeValidation()
        ));

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        $individu = $admissionValidation->getAdmission()->getIndividu()->getId();

        return $this->redirect()->toRoute('admission/document', ['individu' => $individu]);
    }
}
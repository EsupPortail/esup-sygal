<?php

namespace Admission\Controller\Validation;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Etat;
use Admission\Entity\Db\TypeValidation;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Application\Controller\AbstractController;
use Laminas\Http\Response;
use UnicaenApp\Service\EntityManagerAwareTrait;

class AdmissionValidationController extends AbstractController
{
    use AdmissionServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use TypeValidationServiceAwareTrait;
    use EntityManagerAwareTrait;

    public function validerAction(): Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        /** @var TypeValidation $typeValidation */
        $typeValidationId = $this->params('typeValidation');
        $typeValidation = $this->typeValidationService->getRepository()->findTypeValidationById($typeValidationId);

        $redirectUrl = $this->params()->fromQuery('redirect');

        if(empty($admission->getInscription()->first())){
            $this->flashMessenger()->addErrorMessage("Vous devez d'abord renseigner les informations du formulaire avant de pouvoir valider le formulaire");
            return $this->redirect()->toUrl($redirectUrl);
        }

        $admissionValidation = $this->admissionValidationService->newAdmissionValidation($admission, $typeValidation);
        $this->admissionValidationService->saveNewAdmissionValidation($admissionValidation);

        switch ($typeValidation->getCode()) {
            //Remise du dossier d'admission dans l'état "En cours de validation"
            case TypeValidation::CODE_VALIDATION_GESTIONNAIRE:
                $enCoursDeValidation = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS_VALIDATION]);
                $admission->setEtat($enCoursDeValidation);
                $this->admissionService->update($admission);
                break;
            //Remise du dossier d'admission dans l'état "Validé"
            case TypeValidation::CODE_SIGNATURE_PRESIDENT:
                $valide = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_VALIDE]);
                $admission->setEtat($valide);
                $this->admissionService->update($admission);
                break;

            default:
                break;
        }

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

        if(empty($admissionValidation->getAdmission()->getInscription()->first())){
            $this->flashMessenger()->addErrorMessage("Vous devez d'abord renseigner les informations du formulaire avant de pouvoir valider le formulaire");
            return $this->redirect()->toUrl($redirectUrl);
        }

        $this->admissionValidationService->deleteAdmissionValidation($admissionValidation);
        $event = $this->admissionValidationService->triggerEventValidationSupprimee($admissionValidation);

        //Remise du dossier d'admission dans l'état "En cours de saisie"
        if($admissionValidation->getTypeValidation()->getCode() == TypeValidation::CODE_VALIDATION_GESTIONNAIRE){
            /** @var Etat $enCoursDeValidation */
            $enCoursDeValidation = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS_SAISIE]);
            $admissionValidation->getAdmission()->setEtat($enCoursDeValidation);
            $this->admissionService->update($admissionValidation->getAdmission());
        }

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
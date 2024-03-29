<?php

namespace RapportActivite\Controller\Validation;

use Application\Controller\AbstractController;
use Application\Entity\Db\TypeValidation;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Laminas\Http\Response;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteValidationController extends AbstractController
{
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;

    public function validerAction(): Response
    {
        $rapport = $this->requestedRapport();

        /** @var TypeValidation $typeValidation */
        $typeValidationId = $this->params('typeValidation');
        $typeValidation = $this->validationService->findTypeValidationById($typeValidationId);

        $redirectUrl = $this->params()->fromQuery('redirect');

        $rapportValidation = $this->rapportActiviteValidationService->newRapportValidation($rapport, $typeValidation);
        $this->rapportActiviteValidationService->saveNewRapportValidation($rapportValidation);
        $event = $this->rapportActiviteValidationService->triggerEventValidationAjoutee($rapportValidation);

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "%s enregistrée avec succès.",
            $rapportValidation->getTypeValidation()
        ));

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('these/identite', ['these' => $rapport->getThese()->getId()]);
    }

    public function devaliderAction(): Response
    {
        $rapportValidation = $this->requestedRapportValidation();
        $rapport = $rapportValidation->getRapportActivite();
        $redirectUrl = $this->params()->fromQuery('redirect');

        $this->rapportActiviteValidationService->deleteRapportValidation($rapportValidation);
        $event = $this->rapportActiviteValidationService->triggerEventValidationSupprimee($rapportValidation);

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "%s supprimée avec succès.",
            $rapportValidation->getTypeValidation()
        ));

        if ($messages = $event->getMessages()) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }

        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('these/identite', ['these' => $rapport->getThese()->getId()]);
    }

    /**
     * @return RapportActivite
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');

        $rapport = $this->rapportActiviteService->fetchRapportById($id);
        if ($rapport === null) {
            throw new RuntimeException("Aucun rapport trouvé avec l'id spécifié");
        }

        return $rapport;
    }

    /**
     * @return RapportActiviteValidation
     */
    private function requestedRapportValidation(): RapportActiviteValidation
    {
        $id = $this->params()->fromRoute('rapportValidation') ?: $this->params()->fromQuery('rapportValidation');

        /** @var RapportActiviteValidation $rapportValidation */
        $rapportValidation = $this->rapportActiviteValidationService->getRepository()->find($id);
        if ($rapportValidation === null) {
            throw new RuntimeException("Aucune validation trouvée avec cet id");
        }

        return $rapportValidation;
    }
}
<?php

namespace RapportActivite\Controller\Validation;

use Application\Controller\AbstractController;
use Application\Entity\Db\TypeValidation;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Laminas\Http\Response;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Event\Validation\RapportActiviteValidationEvent;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteValidationController extends AbstractController
{
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;

    const RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT = 'RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT';
    const RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT = 'RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT';

    public function validerAction(): Response
    {
        $rapport = $this->requestedRapport();

        /** @var TypeValidation $typeValidation */
        $typeValidationId = $this->params('typeValidation');
        $typeValidation = $this->validationService->findTypeValidationById($typeValidationId);

        $redirectUrl = $this->params()->fromQuery('redirect');

        $this->rapportActiviteValidationService->setTypeValidation($typeValidation);
        $rapportValidation = $this->rapportActiviteValidationService->createForRapportActivite($rapport);

        // déclenchement d'un événement
        $event = $this->triggerEvent(
            self::RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT,
            $rapportValidation,
            []
        );

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "La validation du rapport '%s' a été enregistrée avec succès.",
            $rapport->getFichier()->getNom()
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
        $rapport = $rapportValidation->getRapport();
        $redirectUrl = $this->params()->fromQuery('redirect');

        $this->rapportActiviteValidationService->delete($rapportValidation);

        // déclenchement d'un événement
        $event = $this->triggerEvent(
            self::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
            $rapportValidation,
            []
        );

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "La validation du rapport '%s' a été supprimée avec succès.",
            $rapport->getFichier()->getNom()
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

    private function triggerEvent(string $name, $target, array $params = []): RapportActiviteValidationEvent
    {
        $event = new RapportActiviteValidationEvent($name, $target, $params);

        $this->events->triggerEvent($event);

        return $event;
    }

    /**
     * @return RapportActivite
     */
    private function requestedRapport(): RapportActivite
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportActiviteService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", null, $e);
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
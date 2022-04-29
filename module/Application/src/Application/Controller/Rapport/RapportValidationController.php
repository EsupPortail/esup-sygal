<?php

namespace Application\Controller\Rapport;

use Application\Controller\AbstractController;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\RapportValidation;
use Application\Entity\Db\TypeValidation;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Application\Service\RapportValidation\RapportValidationServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Response;

class RapportValidationController extends AbstractController
{
    use RapportServiceAwareTrait;
    use RapportValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;

    public function validerAction(): Response
    {
        $rapport = $this->requestedRapport();

        /** @var TypeValidation $typeValidation */
        $typeValidationId = $this->params('typeValidation');
        $typeValidation = $this->validationService->findTypeValidationById($typeValidationId);

        $redirectUrl = $this->params()->fromQuery('redirect');

        $this->rapportValidationService->setTypeValidation($typeValidation);
        $this->rapportValidationService->createForRapport($rapport);

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "La validation du rapport '%s' a été enregistrée avec succès.",
            $rapport->getFichier()->getNom()
        ));

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

        $this->rapportValidationService->delete($rapportValidation);

        $this->flashMessenger()->addSuccessMessage(sprintf(
            "La validation du rapport '%s' a été supprimée avec succès.",
            $rapport->getFichier()->getNom()
        ));

        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $this->redirect()->toRoute('these/identite', ['these' => $rapport->getThese()->getId()]);
    }

    /**
     * @return Rapport
     */
    private function requestedRapport(): Rapport
    {
        $id = $this->params()->fromRoute('rapport') ?: $this->params()->fromQuery('rapport');
        try {
            $rapport = $this->rapportService->findRapportById($id);
        } catch (NoResultException $e) {
            throw new RuntimeException("Aucun rapport trouvé avec cet id", null, $e);
        }

        return $rapport;
    }

    /**
     * @return RapportValidation
     */
    private function requestedRapportValidation(): RapportValidation
    {
        $id = $this->params()->fromRoute('rapportValidation') ?: $this->params()->fromQuery('rapportValidation');

        /** @var RapportValidation $rapportValidation */
        $rapportValidation = $this->rapportValidationService->getRepository()->find($id);
        if ($rapportValidation === null) {
            throw new RuntimeException("Aucune validation trouvée avec cet id");
        }

        return $rapportValidation;
    }
}
<?php

namespace RapportActivite\Assertion\Validation;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Service\Validation\ValidationServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Assertion\RapportActiviteOperationAbstractAssertion;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;

class RapportActiviteValidationAssertion extends RapportActiviteOperationAbstractAssertion
{
    use RapportActiviteValidationServiceAwareTrait;
    use ValidationServiceAwareTrait;

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        switch ($action) {
            case 'valider':
                $rapportActivite = $this->rapportActiviteService->fetchRapportById($this->getRouteMatch()->getParam('rapport'));
                if ($rapportActivite === null) {
                    return false;
                }
                $typeValidation = $this->validationService->findTypeValidationById($this->getRouteMatch()->getParam('typeValidation'));
                if ($typeValidation === null) {
                    return false;
                }
                $rapportActiviteValidation = new RapportActiviteValidation($typeValidation, $rapportActivite); // prototype
                $these = $rapportActivite->getThese();
                break;

            case 'devalider':
                $rapportActiviteValidation = $this->rapportActiviteValidationService->getRepository()->find($this->getRouteMatch()->getParam('rapportValidation'));
                $these = $rapportActiviteValidation->getRapportActivite()->getThese();
                break;

            default:
                throw new InvalidArgumentException("Action inattendue : " . $action);
        }

        try {

            switch ($action) {
                case 'valider':
                case 'devalider':
                    $this->assertEtatThese($these);
                    $this->assertAppartenanceThese($these);
            }

            switch ($action) {
                case 'valider':
                    $nextOperation = $this->findNextExpectedOperation($rapportActiviteValidation->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteValidation, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($rapportActiviteValidation);
                    break;
            }

            switch ($action) {
                case 'devalider':
                    $lastCompletedOperation = $this->findLastCompletedOperation($rapportActiviteValidation->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteValidation, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($rapportActiviteValidation);
                    break;
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /**
     * @param RapportActiviteValidation $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        /** @var RapportActiviteValidation $rapportActiviteValidation */
        $rapportActiviteValidation = $entity;

        try {

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT:
                    $this->assertEtatThese($rapportActiviteValidation->getRapportActivite()->getThese());
                    $this->assertAppartenanceThese($rapportActiviteValidation->getRapportActivite()->getThese());
                    break;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                    $nextOperation = $this->findNextExpectedOperation($rapportActiviteValidation->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteValidation, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($rapportActiviteValidation);
                    // IMPORTANT : pour une création, pas de vérification portant sur l'opération suivante.
                    break;

                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT:
                    $lastCompletedOperation = $this->findLastCompletedOperation($rapportActiviteValidation->getRapportActivite());
                    $this->assertOperationsMatch($rapportActiviteValidation, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($rapportActiviteValidation);
                    break;
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }
}
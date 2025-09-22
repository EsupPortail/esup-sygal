<?php

namespace Admission\Assertion\Validation;

use Admission\Assertion\AdmissionOperationAbstractAssertion;
use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Verification;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Assertion\Exception\FailedAssertionException;
use Doctrine\ORM\Exception\NotSupported;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class AdmissionValidationAssertion extends AdmissionOperationAbstractAssertion
{
    use AdmissionValidationServiceAwareTrait;
    use TypeValidationServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use VerificationServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     * @throws NotSupported
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        switch ($action) {
            case 'valider':
                $admission = $this->admissionService->getRepository()->find($this->getRouteMatch()->getParam('admission'));
                if ($admission === null) {
                    return false;
                }
                $typeValidation = $this->typeValidationService->getRepository()->findTypeValidationById($this->getRouteMatch()->getParam('typeValidation'));
                if ($typeValidation === null) {
                    return false;
                }
                $admissionValidation = new AdmissionValidation($typeValidation, $admission); // prototype
                break;

            case 'devalider':
                $admissionValidation = $this->admissionValidationService->getRepository()->find($this->getRouteMatch()->getParam('admissionValidation'));
                $admission = $admissionValidation->getAdmission();
                break;

            default:
                throw new InvalidArgumentException("Action inattendue : " . $action);
        }

        try {

            switch ($action) {
                case 'valider':
                case 'devalider':
                    $this->assertEtatAdmission($admission);
                    $this->assertAppartenanceAdmission($admission);
                    break;
            }

            switch ($action) {
                case 'valider':
                    $this->assertDossierComplet($admissionValidation->getAdmission());
                    $nextOperation = $this->findNextExpectedOperation($admissionValidation->getAdmission());
                    $this->assertOperationsMatch($admissionValidation, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($admissionValidation);
                    break;
            }

            switch ($action) {
                case 'devalider':
                    $lastCompletedOperation = $this->findLastCompletedOperation($admissionValidation->getAdmission());
                    $this->assertOperationsMatch($admissionValidation, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($admissionValidation);
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
     * @param AdmissionValidation $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        /** @var AdmissionValidation $admissionValidation */
        $admissionValidation = $entity;

        try {

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_VALIDER_SIEN:
                case AdmissionPrivileges::ADMISSION_VALIDER_TOUT:
                case AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN:
                case AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT:
                    $this->assertEtatAdmission($admissionValidation->getAdmission());
                    $this->assertAppartenanceAdmission($admissionValidation->getAdmission());
                    break;
            }

            switch ($privilege) {
                case AdmissionPrivileges::ADMISSION_VALIDER_SIEN:
                case AdmissionPrivileges::ADMISSION_VALIDER_TOUT:
                    $this->assertDossierComplet($admissionValidation->getAdmission());
                    $nextOperation = $this->findNextExpectedOperation($admissionValidation->getAdmission());
                    $this->assertOperationsMatch($admissionValidation, $nextOperation);
                    $this->assertOperationIsAllowed($nextOperation);
                    $this->assertPrecedingOperationValueCompatible($admissionValidation);
                    // IMPORTANT : pour une création, pas de vérification portant sur l'opération suivante.
                    break;

                case AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN:
                case AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT:
                    $lastCompletedOperation = $this->findLastCompletedOperation($admissionValidation->getAdmission());
                    $this->assertOperationsMatch($admissionValidation, $lastCompletedOperation);
                    $this->assertOperationIsAllowed($admissionValidation);
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

    private function assertDossierComplet(Admission $admission): void
    {
        $verifs = $this->verificationService->getRepository()->findAllByAdmission($admission);
        $this->assertTrue(
            count(array_filter($verifs, fn(Verification $v) => !$v->getEstComplet())) === 0
        );
    }
}
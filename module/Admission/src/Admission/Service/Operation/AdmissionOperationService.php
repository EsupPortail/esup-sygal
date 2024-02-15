<?php

namespace Admission\Service\Operation;

use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Avis\AdmissionAvisServiceAwareTrait;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use InvalidArgumentException;
use Admission\Entity\Db\Admission;
use Admission\Event\AdmissionEvent;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class AdmissionOperationService
{
    use TypeValidationServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use AdmissionAvisServiceAwareTrait;
    use AvisServiceAwareTrait;

    private array $typeValidationsCache = [];
    private array $avisTypesCache = [];

    public function fetchOperationForAdmissionAndConfig(Admission $admission, array $operationConfig): ?AdmissionOperationInterface
    {
        $typeOperation = $this->fetchTypeOperationFromConfig($operationConfig);

        switch ($operationConfig['type']) {
            // NB : on parcourt les entités liées donc attention à faire les jointure en amont
            case AdmissionValidation::class:
                return $admission->getAdmissionValidationOfType($typeOperation);
            case AdmissionAvis::class:
                return $admission->getAdmissionAvisOfType($typeOperation);
            default:
                throw new InvalidArgumentException("Type inattendu : " . $operationConfig['type']);
        }
    }

    public function newOperationForAdmissionAndConfig(Admission $admission, array $config): AdmissionOperationInterface
    {

        switch ($config['type']) {
            case AdmissionValidation::class:
                $typeValidation = $this->typeValidationService->findTypeValidationByCode($config['code']);
                $ope = new AdmissionValidation($typeValidation, $admission);
                break;
            case AdmissionAvis::class:
                $avisType = $this->avisService->findOneAvisTypeByCode($config['code']);
                $ope = new AdmissionAvis($admission);
                $ope->setAvis((new Avis())->setAvisType($avisType));
                break;
            default:
                throw new InvalidArgumentException("Type inattendu : " . $config['type']);
        }

        return $ope;
    }

    public function deleteOperation(AdmissionOperationInterface $operation)
    {
        switch (true) {
            case $operation instanceof AdmissionValidation:
                $this->admissionValidationService->deleteAdmissionValidation($operation);
                break;
            case $operation instanceof AdmissionAvis:
                $this->admissionAvisService->deleteAdmissionAvis($operation);
                break;
            default:
                throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }
    }

    public function deleteOperationAndThrowEvent(AdmissionOperationInterface $operation, array $messages = []): AdmissionEvent
    {
        $this->deleteOperation($operation);

        switch (true) {
            case $operation instanceof AdmissionValidation:
                $event = $this->admissionValidationService->triggerEventValidationSupprimee($operation, ['messages' => $messages]);
                break;
            case $operation instanceof AdmissionAvis:
                $event = $this->admissionAvisService->triggerEventAvisSupprime($operation, ['messages' => $messages]);
                break;
            default:
                throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }

        return $event;
    }

    public function fetchTypeOperationFromConfig(array $operationConfig)
    {
        switch ($operationConfig['type']) {
            case AdmissionValidation::class:
                return $this->fetchTypeValidationByCode($operationConfig['code']);
            case AdmissionAvis::class:
                return $this->fetchAvisTypeByCode($operationConfig['code']);
            default:
                throw new InvalidArgumentException("Type inattendu : " . $operationConfig['type']);
        }
    }

    private function fetchTypeValidationByCode(string $code): TypeValidation
    {
        if (!array_key_exists($code, $this->typeValidationsCache)) {
            $this->typeValidationsCache[$code] = $this->typeValidationService->findTypeValidationByCode($code);
        }
        return $this->typeValidationsCache[$code];
    }

    private function fetchAvisTypeByCode(string $code): AvisType
    {
        if (!array_key_exists($code, $this->avisTypesCache)) {
            $this->avisTypesCache[$code] = $this->avisService->findOneAvisTypeByCode($code);
        }
        return $this->avisTypesCache[$code];
    }

    public function hideOperations(array $operations, array $operationToHide){
        foreach ($operations as $key => $value) {
            if (in_array($key, $operationToHide)) {
                unset($operations[$key]);
            }
        }
        return $operations;
    }
}
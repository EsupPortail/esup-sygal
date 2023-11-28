<?php

namespace Admission\Service\Operation;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use InvalidArgumentException;
use Admission\Entity\Db\Admission;
use Admission\Event\AdmissionEvent;

class AdmissionOperationService
{
    use TypeValidationServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    private array $typeValidationsCache = [];

    public function fetchOperationForAdmissionAndConfig(Admission $admission, array $operationConfig): ?AdmissionOperationInterface
    {
        $typeOperation = $this->fetchTypeOperationFromConfig($operationConfig);

        switch ($operationConfig['type']) {
            // NB : on parcourt les entités liées donc attention à faire les jointure en amont
            case AdmissionValidation::class:
                return $admission->getAdmissionValidationOfType($typeOperation);
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
}
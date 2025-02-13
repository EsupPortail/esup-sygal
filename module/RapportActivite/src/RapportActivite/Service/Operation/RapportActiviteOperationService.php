<?php

namespace RapportActivite\Service\Operation;

use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationServiceAwareTrait;

class RapportActiviteOperationService
{
    use AvisServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;

    private array $typeValidationsCache = [];
    private array $avisTypesCache = [];

    public function fetchOperationForRapportAndConfig(RapportActivite $rapportActivite, array $operationConfig): ?RapportActiviteOperationInterface
    {
        $typeOperation = $this->fetchTypeOperationFromConfig($operationConfig);

        switch ($operationConfig['type']) {
            // NB : on parcourt les entités liées donc attention à faire les jointure en amont
            case RapportActiviteValidation::class:
                return $rapportActivite->getRapportValidationOfType($typeOperation);
            case RapportActiviteAvis::class:
                return $rapportActivite->getRapportAvisOfType($typeOperation);
            default:
                throw new InvalidArgumentException("Type inattendu : " . $operationConfig['type']);
        }
    }

    public function newOperationForRapportAndConfig(RapportActivite $rapportActivite, array $config): RapportActiviteOperationInterface
    {
        switch ($config['type']) {
            case RapportActiviteValidation::class:
                $typeValidation = $this->validationService->findTypeValidationByCode($config['code']);
                $ope = new RapportActiviteValidation($typeValidation, $rapportActivite);
                break;
            case RapportActiviteAvis::class:
                $avisType = $this->avisService->findOneAvisTypeByCode($config['code']);
                $ope = new RapportActiviteAvis($rapportActivite);
                $ope->setAvis((new Avis())->setAvisType($avisType));
                break;
            default:
                throw new InvalidArgumentException("Type inattendu : " . $config['type']);
        }

        return $ope;
    }

    public function deleteOperation(RapportActiviteOperationInterface $operation)
    {
        switch (true) {
            case $operation instanceof RapportActiviteValidation:
                $this->rapportActiviteValidationService->deleteRapportValidation($operation);
                break;
            case $operation instanceof RapportActiviteAvis:
                $this->rapportActiviteAvisService->deleteRapportAvis($operation);
                break;
            default:
                throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }
    }

    public function deleteOperationAndThrowEvent(RapportActiviteOperationInterface $operation, array $messages = []): RapportActiviteEvent
    {
        $this->deleteOperation($operation);

        switch (true) {
            case $operation instanceof RapportActiviteValidation:
                $event = $this->rapportActiviteValidationService->triggerEventValidationSupprimee($operation, ['messages' => $messages]);
                break;
            case $operation instanceof RapportActiviteAvis:
                $event = $this->rapportActiviteAvisService->triggerEventAvisSupprime($operation, ['messages' => $messages]);
                break;
            default:
                throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }

        return $event;
    }

    public function fetchTypeOperationFromConfig(array $operationConfig)
    {
        switch ($operationConfig['type']) {
            case RapportActiviteValidation::class:
                return $this->fetchTypeValidationByCode($operationConfig['code']);
            case RapportActiviteAvis::class:
                return $this->fetchAvisTypeByCode($operationConfig['code']);
            default:
                throw new InvalidArgumentException("Type inattendu : " . $operationConfig['type']);
        }
    }

    private function fetchTypeValidationByCode(string $code): TypeValidation
    {
        if (!array_key_exists($code, $this->typeValidationsCache)) {
            $this->typeValidationsCache[$code] = $this->validationService->findTypeValidationByCode($code);
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
}
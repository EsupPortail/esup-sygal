<?php

namespace RapportActivite\Service\Operation;

use Application\Service\Validation\ValidationServiceAwareTrait;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Service\AvisServiceAwareTrait;

class RapportActiviteOperationService
{
    use AvisServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteValidationServiceAwareTrait;

    private array $typeValidationsCache = [];
    private array $avisTypesCache = [];

    public function fetchOperationForRapportAndConfig(RapportActivite $rapportActivite, array $config): ?RapportActiviteOperationInterface
    {
        switch ($config['type']) {
            case RapportActiviteValidation::class:
                $typeValidation = $this->fetchTypeValidationByCode($config['code']);
//                $ope = $this->rapportActiviteValidationService->findByRapportActiviteAndType($rapportActivite, $typeValidation);
                // NB : on parcourt les entités liées donc attention à faire les jointure en amont
                $ope = $rapportActivite->getRapportValidationOfType($typeValidation);
                break;
            case RapportActiviteAvis::class:
                $avisType = $this->fetchAvisTypeByCode($config['code']);
//                $ope = $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapportActivite, $avisType);
                // NB : on parcourt les entités liées donc attention à faire les jointure en amont
                $ope = $rapportActivite->getRapportAvisOfType($avisType);
                break;
            default:
                throw new InvalidArgumentException("Type inattendu : " . $config['type']);
        }

        return $ope;
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

    private function fetchTypeValidationByCode(string $code)
    {
        if (!array_key_exists($code, $this->typeValidationsCache)) {
            $this->typeValidationsCache[$code] = $this->validationService->findTypeValidationByCode($code);
        }
        return $this->typeValidationsCache[$code];
    }

    private function fetchAvisTypeByCode(string $code)
    {
        if (!array_key_exists($code, $this->avisTypesCache)) {
            $this->avisTypesCache[$code] = $this->avisService->findOneAvisTypeByCode($code);
        }
        return $this->avisTypesCache[$code];
    }
}
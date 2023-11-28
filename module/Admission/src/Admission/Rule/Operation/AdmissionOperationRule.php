<?php

namespace Admission\Rule\Operation;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Service\Operation\AdmissionOperationServiceAwareTrait;
use Application\Entity\Db\Role;
use InvalidArgumentException;
use Admission\Entity\Db\Admission;
use Webmozart\Assert\Assert;

class AdmissionOperationRule
{
    use AdmissionOperationServiceAwareTrait;

    /**
     * Config de l'enchaînement des validations attendus.
     */
    private array $operationsConfig;

    /**
     * Cache des opérations par dossier d'admission d'activité.
     * @var AdmissionOperationInterface[]
     */
    private ?array $operations = null;

    public function __construct(array $operationsConfig)
    {
        $this->operationsConfig = $operationsConfig;
    }

    /**
     * Injecte dans le dossier d'admission spécifié les données indiquant l'opération réalisable sur le dossier d'admission spécifié.
     */
    public function injectOperationPossible(Admission $admission)
    {
        $operation = $this->findNextExpectedOperation($admission);
        $admission->setOperationPossible($operation);
    }

    /**
     * Recherche la prochaine opération attendue pour le dossier d'admission spécifié.
     */
    public function findNextExpectedOperation(Admission $admission): ?AdmissionOperationInterface
    {
        // recherche de la 1ere opération non réalisée (càd dont l'id est null)
        foreach ($this->getOperationsForAdmission($admission) as $operation) {
            if ($operation->getId() === null) {
                return $operation;
            }
        }

        return null;
    }

    /**
     * @param Admission $admission
     * @return bool
     */
    public function isLastCompletedOperationValueCompatible(Admission $admission): bool
    {
        $operation = $this->findLastCompletedOperation($admission);

        // si aucune opération réalisée n'est trouvée, champ libre
        if ($operation === null) {
            return true;
        }

        return $operation->getValeurBool() === true;
    }

    /**
     * Retourne la dernière opération réalisée sur le dossier d'admission spécifié,
     * ou `null` si aucune opération n'a été réalisée.
     */
    public function findLastCompletedOperation(Admission $admission): ?AdmissionOperationInterface
    {
        $prevOperation = null;
        foreach ($this->getOperationsForAdmission($admission) as $operation) {
            if ($operation->getId() === null) {
                break;
            }
            $prevOperation = $operation;
        }

        return $prevOperation;
    }

    public function isPrecedingOperationValueCompatible(AdmissionOperationInterface $operation): bool
    {
        $precedingOperation = $this->findPrecedingOperation($operation);
        if ($precedingOperation === null) {
            return true;
        }
        if ($precedingOperation->getId() === null) {
            throw new InvalidArgumentException("Mauvais usage, l'opération précédente devrait être réalisée !");
        }

        return $this->checkPreConditionForOperation($operation);
    }

    public function isFollowingOperationValueCompatible(AdmissionOperationInterface $operation): bool
    {
        $followingOperation = $this->findFollowingOperation($operation);
        if ($followingOperation === null) {
            return true;
        }
        if ($followingOperation->getId() === null) {
            return true;
        }

        // on décide qu'une opération n'est plus *modifiable* dès lors qu'elle est suivie d'une opération réalisée
        return false;
    }

    /**
     * Recherche l'éventuelle opération située avant celle spécifiée.
     */
    public function findPrecedingOperation(AdmissionOperationInterface $operation): ?AdmissionOperationInterface
    {
        $precOperation = null;
        foreach ($this->getOperationsForAdmission($operation->getAdmission()) as $ope) {
            if ($operation->matches($ope)) {
                return $precOperation;
            }
            $precOperation = $ope;
        }

        return null;
    }

    /**
     * Recherche l'éventuelle opération située après celle spécifiée.
     */
    public function findFollowingOperation(AdmissionOperationInterface $operation): ?AdmissionOperationInterface
    {
        $nextOperation = null;
        $found = false;
        foreach ($this->getOperationsForAdmission($operation->getAdmission()) as $ope) {
            if ($found) {
                $nextOperation = $ope;
                break;
            }
            if ($operation->matches($ope)) {
                $found = true;
            }
        }

        return $nextOperation;
    }

    /**
     * @param AdmissionOperationInterface $operation
     * @param Role $role
     * @return bool
     */
    public function isOperationAllowedByRole(AdmissionOperationInterface $operation, Role $role): bool
    {
        $name = $this->findOperationName($operation);

        if (!in_array($role->getCode(), (array) $this->operationsConfig[$name]['role'])) {
            return false;
        }

        return true;
    }

    /**
     * @param AdmissionOperationInterface $operation
     * @return bool
     */
    public function isOperationReadonly(AdmissionOperationInterface $operation): bool
    {
        $name = $this->findOperationName($operation);

        return $this->operationsConfig[$name]['is_readonly'] ?? false;
    }

    /**
     * Recherche dans la config le nom d'une opération, avec comme critères :
     *   - le 'type'
     *   - le 'code'
     */
    public function findOperationName(AdmissionOperationInterface $operation): ?string
    {
        if ($operation instanceof AdmissionValidation) {
            $comparator = fn(AdmissionValidation $ope, array $config) =>
                $ope instanceof $config['type'] &&
                $config['code'] ===  $ope->getTypeValidation()->getCode();
        } else {
            throw new InvalidArgumentException("Type d'opération inattendu : " . get_class($operation));
        }

        $found = [];
        foreach ($this->operationsConfig as $name => $config) {
            if ($comparator($operation, $config)) {
                $found[] = $name;
            }
        }

        if (empty($found)) {
            return null;
        }

        Assert::count($found, 1);

        return $found[0];
    }

    /**
     * @param TypeValidation|string $typeOperation
     * @return array
     */
    public function getConfigForTypeOperation($typeOperation): array
    {
        $code = is_string($typeOperation) ? $typeOperation : $typeOperation->getCode();

        foreach ($this->operationsConfig as $operationConfig) {
            // NB : l'absence de doublons de codes dans la config est garantie par construction.
            if ($operationConfig['code'] === $code) {
                return $operationConfig;
            }
        }
        throw new InvalidArgumentException("Type d'opération non trouvé dans la config : '$code'");
    }

    public function getConfigForOperation(AdmissionOperationInterface $operation): array
    {
        $name = $this->findOperationName($operation);

        return $this->getConfigForOperationName($name);
    }

    public function getConfigForOperationName(string $name): array
    {
        if (!array_key_exists($name, $this->operationsConfig)) {
            throw new InvalidArgumentException("Aucune config d'opération trouvée avec ce nom : '$name'");
        }

        // NB : injection du 'name' dans la config
        $this->operationsConfig[$name]['name'] = $name;

        return $this->operationsConfig[$name];
    }

    /**
     * @param Admission $admission
     * @return AdmissionOperationInterface[]
     */
    public function getOperationsForAdmission(Admission $admission): array
    {
        if ($this->operations === null) {
            $this->operations = [];
        }
        if (!array_key_exists($admission->getId(), $this->operations)) {
            $this->operations[$admission->getId()] = null;
        }

        if ($this->operations[$admission->getId()] !== null) {
            return $this->operations[$admission->getId()];
        }

        $this->loadOperationsForAdmission($admission);

        return $this->operations[$admission->getId()];
    }

    private function loadOperationsForAdmission(Admission $admission): void
    {
        $this->operations[$admission->getId()] = [];

        foreach ($this->operationsConfig as $name => $config) {
            if (!$this->isOperationEnabledForAdmission($config, $admission)) {
                continue;
            }

            $ope = $this->admissionOperationService->fetchOperationForAdmissionAndConfig($admission, $config);

            // Si aucune données trouvée pour l'opération considérée, c'est quelle n'a pas été "réalisée",
            // on instancie alors un prototype (càd dont l'id est null).
            if ($ope === null) {
                $ope = $this->admissionOperationService->newOperationForAdmissionAndConfig($admission, $config);
            }

            $this->operations[$admission->getId()][$name] = $ope;
        }
    }

    public function isOperationEnabledForAdmission(array $operationConfig, Admission $admission)
    {
        if (is_bool($operationConfig['enabled'])) {
            return $operationConfig['enabled'];
        } elseif (is_callable($operationConfig['enabled'])) {
            return call_user_func($operationConfig['enabled'], $admission);
        }
        return true;
    }

    private function checkPreConditionForOperation(AdmissionOperationInterface $operation): bool
    {
        $name = $this->findOperationName($operation);

        if (!array_key_exists('pre_condition', $this->operationsConfig[$name])) {
            return true;
        }

        $preCondition = $this->operationsConfig[$name]['pre_condition'];
        if (!$preCondition) {
            return true;
        }

        if (is_callable($preCondition)) {
            return call_user_func($preCondition, $operation->getAdmission());
        }

        if (!is_array($preCondition)) {
            $preCondition = [$preCondition => null]; // i.e. pas de condition
        }

        $operations = $this->getOperationsForAdmission($operation->getAdmission());

        $result = true;
        foreach ($preCondition as $name => $spec) {
            if ($spec === null) {
                // i.e. pas de condition
                continue;
            }

            // Si l'opération mentionnée n'est pas activée, pas la peine de la prendre en compte.
            if (!$this->isOperationEnabledForAdmission($this->operationsConfig[$name], $operation->getAdmission())) {
                continue;
            }

            $ope = $operations[$name];

            if ($ope->getId() === null) {
                // si l'opération n'est pas réalisée, basta
                $result = false;
                break;
            }

            if (is_bool($spec)) {
                $result = $result && $ope->getValeurBool() === $spec;
            } else {
                throw new InvalidArgumentException("Type de précondition spécifiée inattendu : " . gettype($spec));
            }
        }

        return $result;
    }

    /**
     * @return TypeValidation[]
     */
    public function fetchTypesOperation(): array
    {
        return array_map(
            fn(array $config) => $this->admissionOperationService->fetchTypeOperationFromConfig($config),
            $this->operationsConfig
        );
    }
}
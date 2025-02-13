<?php

namespace RapportActivite\Rule\Operation;

use Application\Entity\Db\Role;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Service\Operation\RapportActiviteOperationServiceAwareTrait;
use Webmozart\Assert\Assert;

class RapportActiviteOperationRule
{
    use RapportActiviteOperationServiceAwareTrait;

    /**
     * Config de l'enchaînement des validations/avis attendus.
     */
    private array $operationsConfig;

    /**
     * Cache des opérations par rapport d'activité.
     * @var \RapportActivite\Entity\RapportActiviteOperationInterface[]
     */
    private ?array $operations = null;

    public function __construct(array $operationsConfig)
    {
        $this->operationsConfig = $operationsConfig;
    }

    /**
     * Injecte dans le rapport spécifié les données indiquant l'opération réalisable sur le rapport spécifié.
     */
    public function injectOperationPossible(RapportActivite $rapport)
    {
        $operation = $this->findNextExpectedOperation($rapport);
        $rapport->setOperationPossible($operation);
    }

    /**
     * Recherche la prochaine opération attendue pour le rapport spécifié.
     */
    public function findNextExpectedOperation(RapportActivite $rapport): ?RapportActiviteOperationInterface
    {
        // recherche de la 1ere opération non réalisée (càd dont l'id est null)
        foreach ($this->getOperationsForRapport($rapport) as $operation) {
            if ($operation->getId() === null) {
                return $operation;
            }
        }

        return null;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @return bool
     */
    public function isLastCompletedOperationValueCompatible(RapportActivite $rapportActivite): bool
    {
        $operation = $this->findLastCompletedOperation($rapportActivite);

        // si aucune opération réalisée n'est trouvée, champ libre
        if ($operation === null) {
            return true;
        }

        return $operation->getValeurBool() === true;
    }

    /**
     * Retourne la dernière opération réalisée sur le rapport spécifié,
     * ou `null` si aucune opération n'a été réalisée.
     */
    public function findLastCompletedOperation(RapportActivite $rapportActivite): ?RapportActiviteOperationInterface
    {
        $prevOperation = null;
        foreach ($this->getOperationsForRapport($rapportActivite) as $operation) {
            if ($operation->getId() === null) {
                break;
            }
            $prevOperation = $operation;
        }

        return $prevOperation;
    }

    public function isPrecedingOperationValueCompatible(RapportActiviteOperationInterface $operation): bool
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

    public function isFollowingOperationValueCompatible(RapportActiviteOperationInterface $operation): bool
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
    public function findPrecedingOperation(RapportActiviteOperationInterface $operation): ?RapportActiviteOperationInterface
    {
        $precOperation = null;
        foreach ($this->getOperationsForRapport($operation->getRapportActivite()) as $ope) {
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
    public function findFollowingOperation(RapportActiviteOperationInterface $operation): ?RapportActiviteOperationInterface
    {
        $nextOperation = null;
        $found = false;
        foreach ($this->getOperationsForRapport($operation->getRapportActivite()) as $ope) {
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
     * @param \RapportActivite\Entity\RapportActiviteOperationInterface $operation
     * @param \Application\Entity\Db\Role $role
     * @return bool
     */
    public function isOperationAllowedByRole(RapportActiviteOperationInterface $operation, Role $role): bool
    {
        $name = $this->findOperationName($operation);

        if (!in_array($role->getCode(), (array) $this->operationsConfig[$name]['role'])) {
            return false;
        }

        return true;
    }

    /**
     * @param \RapportActivite\Entity\RapportActiviteOperationInterface $operation
     * @return bool
     */
    public function isOperationReadonly(RapportActiviteOperationInterface $operation): bool
    {
        $name = $this->findOperationName($operation);

        return $this->operationsConfig[$name]['is_readonly'] ?? false;
    }

    /**
     * Recherche dans la config le nom d'une opération, avec comme critères :
     *   - le 'type'
     *   - le 'code'
     */
    public function findOperationName(RapportActiviteOperationInterface $operation): ?string
    {
        if ($operation instanceof RapportActiviteValidation) {
            $comparator = fn(RapportActiviteValidation $ope, array $config) =>
                $ope instanceof $config['type'] &&
                $config['code'] ===  $ope->getTypeValidation()->getCode();
        } elseif ($operation instanceof RapportActiviteAvis) {
            $comparator = fn(RapportActiviteAvis $ope, array $config) =>
                $ope instanceof $config['type'] &&
                $config['code'] ===  $ope->getAvis()->getAvisType()->getCode();
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
     * @param \Validation\Entity\Db\TypeValidation|\UnicaenAvis\Entity\Db\AvisType|string $typeOperation
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

    public function getConfigForOperation(RapportActiviteOperationInterface $operation): array
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
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @return \RapportActivite\Entity\RapportActiviteOperationInterface[]
     */
    public function getOperationsForRapport(RapportActivite $rapportActivite): array
    {
        if ($this->operations === null) {
            $this->operations = [];
        }
        if (!array_key_exists($rapportActivite->getId(), $this->operations)) {
            $this->operations[$rapportActivite->getId()] = null;
        }

        if ($this->operations[$rapportActivite->getId()] !== null) {
            return $this->operations[$rapportActivite->getId()];
        }

        $this->loadOperationsForRapport($rapportActivite);

        return $this->operations[$rapportActivite->getId()];
    }

    private function loadOperationsForRapport(RapportActivite $rapportActivite): void
    {
        $this->operations[$rapportActivite->getId()] = [];

        foreach ($this->operationsConfig as $name => $config) {
            if (!$this->isOperationEnabledForRapport($config, $rapportActivite)) {
                continue;
            }

            $ope = $this->rapportActiviteOperationService->fetchOperationForRapportAndConfig($rapportActivite, $config);

            // Si aucune données trouvée pour l'opération considérée, c'est quelle n'a pas été "réalisée",
            // on instancie alors un prototype (càd dont l'id est null).
            if ($ope === null) {
                $ope = $this->rapportActiviteOperationService->newOperationForRapportAndConfig($rapportActivite, $config);
            }

            $this->operations[$rapportActivite->getId()][$name] = $ope;
        }
    }

    public function isOperationEnabledForRapport(array $operationConfig, RapportActivite $rapportActivite)
    {
        if (is_bool($operationConfig['enabled'])) {
            return $operationConfig['enabled'];
        } elseif (is_callable($operationConfig['enabled'])) {
            return call_user_func($operationConfig['enabled'], $rapportActivite);
        }
        return true;
    }

    private function checkPreConditionForOperation(RapportActiviteOperationInterface $operation): bool
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
            return call_user_func($preCondition, $operation->getRapportActivite());
        }

        if (!is_array($preCondition)) {
            $preCondition = [$preCondition => null]; // i.e. pas de condition
        }

        $operations = $this->getOperationsForRapport($operation->getRapportActivite());

        $result = true;
        foreach ($preCondition as $name => $spec) {
            if ($spec === null) {
                // i.e. pas de condition
                continue;
            }

            // Si l'opération mentionnée n'est pas activée, pas la peine de la prendre en compte.
            if (!$this->isOperationEnabledForRapport($this->operationsConfig[$name], $operation->getRapportActivite())) {
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
     * @return \Validation\Entity\Db\TypeValidation[]|\UnicaenAvis\Entity\Db\AvisType[]
     */
    public function fetchTypesOperation(): array
    {
        return array_map(
            fn(array $config) => $this->rapportActiviteOperationService->fetchTypeOperationFromConfig($config),
            $this->operationsConfig
        );
    }
}
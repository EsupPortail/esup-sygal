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

    private array $allowedRoles = [
        Role::CODE_DOCTORANT,
        Role::CODE_DIRECTEUR_THESE,
        Role::CODE_CODIRECTEUR_THESE,
        Role::CODE_RESP_UR,
        Role::CODE_RESP_ED,
        Role::CODE_GEST_ED,
    ];

    /**
     * Config de l'enchaînement des validations/avis attendus.
     * @var array[]
     */
    private array $operationsConfig;

    private ?array $operations = null;

    public function __construct(array $operationsConfig)
    {
        $this->setOperationsConfig($operationsConfig);
    }

    public function setOperationsConfig(array $operationsConfig)
    {
        $this->validateOperationsConfig($operationsConfig);

        $this->operationsConfig = $operationsConfig;
    }

    private function validateOperationsConfig(array $operationsConfig)
    {
        if (empty($operationsConfig)) {
            throw new InvalidArgumentException("Config vide !");
        }

        $distincts = [];

        foreach ($operationsConfig as $config) {
            if (!array_key_exists($k = 'type', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }
            if (!array_key_exists($k = 'code', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }

            if (!array_key_exists($k = 'role', $config)) {
                throw new InvalidArgumentException("Config invalide, clé '$k' introuvable !");
            }
            $this->validateRoles($config['role']);

            if (($distincts[$config['type']] ?? null) === $config['code']) {
                throw new InvalidArgumentException("Config invalide, doublons {type, code} interdits !");
            }
            $distincts[$config['type']] = $config['code'];

        }
    }

    private function validateRoles(array $roles)
    {
        if ($diff = array_diff(array_unique($roles), $this->allowedRoles)) {
            throw new InvalidArgumentException("Les rôles suivants ne sont pas supportés : " . implode(', ', $diff));
        }
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

//        if ($role->getTypeStructureDependant() !== null) {
//            $these = $operation->getRapportActivite()->getThese();
//            if ($role->getTypeStructureDependant()->isUniteRecherche()) {
//                if ($role->getStructure() !== $these->getUniteRecherche()) {
//                    return false;
//                }
//            } elseif ($role->getTypeStructureDependant()->isEcoleDoctorale()) {
//                if ($role->getStructure() !== $these->getEcoleDoctorale()) {
//                    return false;
//                }
//            } else {
//                return false;
//            }
//        } elseif ($role->isDirecteurThese()) {
//            if ()
//        }

        return true;
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

    public function getOperationConfig(RapportActiviteOperationInterface $operation): array
    {
        $name = $this->findOperationName($operation);

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
        foreach ($this->operationsConfig as $name => $config) {
            if (is_bool($config['enabled']) && !$config['enabled']) {
                continue;
            } elseif (is_callable($config['enabled']) && !call_user_func($config['enabled'], $rapportActivite)) {
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
}
<?php

namespace Application\Search\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Représente un filtre de type liste déroulante.
 *
 * @author Unicaen
 */
class SelectSearchFilter extends SearchFilter
{
    public const VALUE_NONE = 'NULL';

    protected ?array $data = null;

    protected bool $multiple = false;

    protected array $options = [];

    protected bool $allowsEmptyOption = true;
    protected string $emptyOptionLabel = "(Peu importe)";

    protected bool $allowsNoneOption = false;
    protected string $noneOptionLabel = "(Non renseigné)";

    protected bool $applyInValueOptionsConditionToQueryBuilder = false;

    /**
     * Active ou non l'application au query builder de la clause garantissant que la valeur de chaque "where field"
     * fait bien partie des valeurs présente dans la liste déroulante, **uniquement dans le cas où l'option "vide"
     * est sélectionnée**.
     *
     * L'activer est utile dans le cas où la liste de valeurs sélectionnables n'est qu'un sous-ensemble des valeurs
     * métiers possibles (déterminé par le rôle de l'utilisateur par ex) et où il faut donc empêcher que la sélection
     * de l'option "Peu importe" remonte des données interdites à l'utilisateur.
     *
     * NB : si {@see setQueryBuilderApplier()} a été utilisée pour fournir un callable, on ne pourra rien faire
     * puisqu'on ne disposera pas des "where fields".
     */
    public function setApplyInValueOptionsConditionToQueryBuilder(bool $applyInValueOptionsConditionToQueryBuilder = true): static
    {
        $this->applyInValueOptionsConditionToQueryBuilder = $applyInValueOptionsConditionToQueryBuilder;
        return $this;
    }

    /**
     * Spécifie si ce filtre permet la sélection mutliple.
     */
    public function setMultiple(?bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Indique si ce filtre permet la sélection mutliple.
     */
    public function multiple(): bool
    {
        return $this->multiple;
    }

    public function getValue(): mixed
    {
        $value = parent::getValue();

        if ($this->multiple) {
            $value = array_filter((array)$value, fn($v) => $v !== '');
        }

        return $value;
    }

    public function setValue($value): static
    {
        // si la liste de valeurs possibles est pré-remplie, verification que la valeur spécifiée est dedans.
        if ($value !== null && is_array($this->data)) {
            $options = $this->createValueOptionsFromData($this->data);
            if ($this->multiple) {
                $value = (array)$value;
                $found = count($value) === count(array_intersect(
                    array_map(fn($item) => $item['value'], $options),
                    $value
                ));
            } else {
                $found = false;
                foreach ($options as $item) {
                    if ($item['value'] === $value) {
                        $found = true;
                        break;
                    }
                }
            }
            Assert::true($found, "Valeur de filtre illégale : " . (is_array($value) ? implode(', ', $value) : $value));
        }

        return parent::setValue($value);
    }

    /**
     * @param SearchFilterValueInterface|null $defaultValue
     * @return self
     */
    public function setDefaultValueAsObject(SearchFilterValueInterface $defaultValue = null): static
    {
        return $this->setDefaultValue($defaultValue ? $defaultValue->createSearchFilterValueOption()['value'] : null);
    }

    /**
     * @var callable|null
     */
    protected $dataProvider;

    /**
     * @param callable $dataProvider function(SelectSearchFilter $filter) { return []; }
     * @return self
     */
    public function setDataProvider(callable $dataProvider): static
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }

    public function fetchData(): array
    {
        if ($this->dataProvider === null) {
            throw new RuntimeException("Aucun 'data provider' fourni pour le filtre '{$this->getName()}'");
        }

        $callable = $this->dataProvider;

        return $callable($this);
    }

    public function applyDefaultsToQueryBuilder(QueryBuilder $qb): void
    {
        if ($this->applyInValueOptionsConditionToQueryBuilder && $this->getValue() === null) {
            $this->applyInValueOptionsConditionToQueryBuilder($qb);
        }
    }

    public function canApplyToQueryBuilder(): bool
    {
        if ($this->multiple) {
            return !empty((array)$this->getValue());
        }

        return $this->getValue() !== null; // NB : '0' est une valeur valide
    }

    /**
     * Application au query builder spécifié de quoi garantir que la valeur fait partie des valeurs sélectionnables.
     * S'il n'y a aucune valeur sélectionnable (hors valeurs '' et 'Aucun'), on génère un IN() retournant toujours faux.
     */
    protected function applyInValueOptionsConditionToQueryBuilder(QueryBuilder $qb): void
    {
        // valeur sélectionnée = "Aucun·e" : la vérification qui suit n'a pas de sens.
        if ($this->getValue() === static::VALUE_NONE) {
            return;
        }

        // si un callable a été spécifié, on n'a pas de "where fields" donc on ne peut rien faire : on couine !
        Assert::null($this->applyToQueryBuilderCallable, 'Opération impossible si un callable a été spécifié.');

        // aucune données : on est obligé de les charger.
        if ($this->data === null) {
            $this->data = $this->fetchData();
        }

        $valueOptions = $this->getValueOptions() ?: $this->createValueOptionsFromData($this->data);

        // normalisation des options sélectionnables
        $whereValues = array_map(fn($value) => $value['value'] ?? $value, $valueOptions);
        // retrait des options "Aucun" et ''
        $whereValues = array_filter($whereValues, fn($value) => !in_array($value, ['', static::VALUE_NONE], true));

        if (empty($whereValues)) {
            // NB : si aucune valeur sélectionnable, on génère un IN() retournant toujours faux
            $whereValues = [uniqid('aucune_valeur_selectionnable_')];
        }

        if ($this->whereFields !== null) {
            $whereFields = current($this->whereFields); // strip OR/AND
            foreach ($whereFields as $whereField) {
                $qb->andWhere($qb->expr()->in($whereField, $whereValues));
            }
        } else {
            $alias = current($qb->getRootAliases());
            $whereField = sprintf('%s.%s', $alias, $this->getName());
            $qb->andWhere($qb->expr()->in($whereField, $whereValues));
        }
    }

    protected function generateParameterName(): ?string
    {
        if ($this->getValue() === self::VALUE_NONE) {
            // aucun paramètre DQL puisqu'on utilise 'is null'
            return null;
        }

        return parent::generateParameterName();
    }

    protected function generateComparisonTemplate(string $operand): string
    {
        if ($this->getValue() === self::VALUE_NONE) {
            return "$operand is null";
        } elseif ($this->multiple) {
            return "$operand in (:%s)";
        } else {
            return parent::generateComparisonTemplate($operand);
        }
    }

    /**
     * Génére à partir des valeurs spécifiées les 'value_options'
     * permettant de peupler la liste déroulante correspondant à ce filtre.
     *
     * @param array $data
     * @return array
     */
    public function createValueOptionsFromData(array $data): array
    {
        $options = [];
        if ($this->allowsEmptyValueOption()) {
            $options[] = static::valueOptionEmpty($this->getEmptyValueOptionLabel());
        }
        if ($this->allowsNoneValueOption()) {
            $options[] = static::valueOptionNone($this->getNoneValueOptionLabel());
        }
        foreach ($data as $key => $value) {
            $options[] = $this->createValueOption($value, $key);
        }

        return $options;
    }

    public function createValueOption($value, $key): array
    {
        if ($value instanceof SearchFilterValueInterface) {
            $valueOption = $value->createSearchFilterValueOption();
            Assert::keyExists($valueOption, 'value');
            Assert::keyExists($valueOption, 'label');
            return $valueOption;
        }

        try {
            $valueToString = (string) $value;
        } catch (Throwable $e) {
            throw new InvalidArgumentException(sprintf(
                "Impossible de convertir en string les valeurs de type '%s'. Vous pouvez implémenter l'interface '%s'.",
                is_object($value) ? get_class($value) : gettype($value),
                SearchFilterValueInterface::class
            ));
        }

        return static::valueOption($valueToString, (string) $key);
    }

    public function init(): static
    {
        // Si ce filtre n'autorise pas la sélection de l'option "vide", alors sélection de la 1ere valeur.
        if (!$this->allowsEmptyValueOption() && is_array($this->data) && !empty($this->data)) {
            $firstValue = reset($this->data);
            $this->setDefaultValue($firstValue->getId());
        }

        return $this;
    }

    /**
     * Retourne les valeurs "objets" possibles pour ce filtre.
     *
     * @return null|array
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Spécifie les valeurs "objets" possibles pour ce filtre.
     *
     * @param array $data
     * @return self
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Retourne les 'value_options' pour ce filtre, utilisables pour transmettre au Select d'un formulaire.
     *
     * @return array
     */
    public function getValueOptions(): array
    {
        return $this->options;
    }

    /**
     * Spécifie les 'value_options' pour ce filtre, utilisables pour transmettre au Select d'un formulaire.
     *
     * @param array $options
     * @return self
     */
    public function setValueOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retourne true si ce filtre autorise la sélection de l'option "vide".
     *
     * @return bool
     */
    public function allowsEmptyValueOption(): bool
    {
        return $this->allowsEmptyOption;
    }

    /**
     * Spécifie si ce filtre autorise la sélection de l'option "vide".
     *
     * @param bool $allowsEmptyOption
     * @return self
     */
    public function setAllowsEmptyValueOption(bool $allowsEmptyOption = true): static
    {
        $this->allowsEmptyOption = $allowsEmptyOption;
        return $this;
    }

    /**
     * Retourne le libellé de l'option "vide", ex: "Toutes" ou "Peu importe".
     *
     * @return string
     */
    public function getEmptyValueOptionLabel(): string
    {
        return $this->emptyOptionLabel;
    }

    /**
     * Spécifie le libellé de l'option "vide", ex: "Toutes" ou "Peu importe".
     *
     * @param string $emptyOptionLabel
     * @return self
     */
    public function setEmptyValueOptionLabel(string $emptyOptionLabel): static
    {
        $this->emptyOptionLabel = $emptyOptionLabel;

        return $this;
    }

    /**
     * Retourne true si ce filtre autorise la sélection de l'option "non renseigné".
     *
     * @return bool
     */
    public function allowsNoneValueOption(): bool
    {
        return $this->allowsNoneOption;
    }

    /**
     * Spécifie si ce filtre autorise la sélection de l'option "non renseigné".
     *
     * @param bool $allowsNoneOption
     * @return self
     */
    public function setAllowsNoneValueOption(bool $allowsNoneOption = true): static
    {
        $this->allowsNoneOption = $allowsNoneOption;
        return $this;
    }

    /**
     * Retourne le libellé de l'option "non renseigné", ex: "Non renseigné".
     *
     * @return string
     */
    public function getNoneValueOptionLabel(): string
    {
        return $this->noneOptionLabel;
    }

    /**
     * Spécifie le libellé de l'option "non renseigné", ex: "Non renseigné".
     *
     * @param string $noneOptionLabel
     * @return self
     */
    public function setNoneValueOptionLabel(string $noneOptionLabel): static
    {
        $this->noneOptionLabel = $noneOptionLabel;

        return $this;
    }

    /**
     * Retourne true si, d'après les valeurs des paramètres GET, l'option spécifiée est sélectionnée.
     *
     * @param mixed $optionValue Valeur de l'option
     * @param string[] $queryParams valeurs des paramètres GET
     * @return bool
     */
    public function isSelectValueOptionActive(mixed $optionValue, array $queryParams): bool
    {
        $optionName = $this->getName();

        if ($this->multiple) {
            return
                ($optionValue !== '' && (isset($queryParams[$optionName]) && in_array($optionValue, (array) $queryParams[$optionName]))) ||
                ($optionValue === '' && (!isset($queryParams[$optionName]) || in_array($optionValue, (array) $queryParams[$optionName])));
        } else {
            return
                ($optionValue !== '' && (isset($queryParams[$optionName]) && $queryParams[$optionName] === $optionValue)) ||
                ($optionValue === '' && (!isset($queryParams[$optionName]) || $queryParams[$optionName] === $optionValue));
        }
    }


    /**
     * @param string $label
     * @return array
     */
    static public function valueOptionNone(string $label = "(Aucun.e)"): array
    {
        return ['value' => self::VALUE_NONE, 'label' => $label];
    }

    /**
     * @param string $label
     * @return array
     */
    static public function valueOptionEmpty(string $label = "(Peu importe)"): array
    {
        return ['value' => '', 'label' => $label];
    }

    /**
     * @param string $label
     * @param string $value DOIT être une chaîne de caractères
     * @return array
     */
    static public function valueOption(string $label, string $value): array
    {
        return ['value' => $value, 'label' => $label];
    }

    /**
     * @param string $string
     * @return array
     */
    static public function valueOptionFromString(string $string): array
    {
        return ['value' => $string, 'label' => $string];
    }
}
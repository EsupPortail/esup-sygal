<?php

namespace Application\Search\Filter;

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
    const VALUE_NULL = 'NULL';

    /**
     * @var array|null
     */
    protected ?array $data = null;

    /**
     * @var string[]
     */
    protected array $options = [];

    protected bool $allowsEmptyOption = true;
    protected string $emptyOptionLabel = "(Peu importe)";
    protected bool $allowsNoneOption = false;
    protected string $noneOptionLabel = "(Non renseigné)";

    public function setValue($value): static
    {
        // si la liste de valeurs possibles est pré-remplie, verification que la valeur spécifiée est dedans.
        if ($value !== null && is_array($this->data)) {
            $options = $this->createValueOptionsFromData($this->data);
            $found = false;
            foreach ($options as $item) {
                if ($item['value'] === $value) {
                    $found = true;
                    break;
                }
            }
            Assert::true($found, "Valeur de filtre illégale : " . $value);
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

    public function loadData(): array
    {
        if ($this->dataProvider === null) {
            throw new RuntimeException("Aucun 'data provider' fourni pour le filtre '{$this->getName()}'");
        }

        $callable = $this->dataProvider;

        return $callable($this);
    }

    public function canApplyToQueryBuilder(): bool
    {
        return $this->getValue() !== null; // '0' est une valeur valide
    }

//    protected function generateQueryBuilderExprAndParamsFromWhereFields(array $whereFields): array
//    {
//        if ($this->getValue() === self::VALUE_NULL) {
//            $params = []; // aucun paramètre de requête puisqu'on compare à NULL
//
//            $exprs = [];
//            foreach ($whereFields as $field) {
//                $exprs[] = sprintf("%s IS NULL", $field);
//            }
//
//            return [$exprs, $params];
//        }
//
//        return parent::generateQueryBuilderExprAndParamsFromWhereFields($whereFields);
//    }

    protected function generateParameterName(): ?string
    {
        if ($this->getValue() === self::VALUE_NULL) {
            // aucun paramètre DQL puisqu'on utilise 'is null'
            return null;
        }

        return parent::generateParameterName();
    }

    protected function generateComparisonTemplate(string $operand): string
    {
        if ($this->getValue() === self::VALUE_NULL) {
            return "$operand is null";
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
            $options[] = static::valueOptionUnknown($this->getNoneValueOptionLabel());
        }
        foreach ($data as $key => $value) {
            $options[] = $this->createValueOption($value, $key);
        }

        return $options;
    }

    public function createValueOption($value, $key): array
    {
        if ($value instanceof SearchFilterValueInterface) {
            return $value->createSearchFilterValueOption();
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
     * Retourne true si, d'après les valeurs des paramètres GET, l'option de ce filtre select est sélectionnée.
     *
     * @param mixed $optionValue Valeur de l'option
     * @param string[] $queryParams valeurs des paramètres GET
     * @return bool
     */
    public function isSelectValueOptionActive(mixed $optionValue, array $queryParams): bool
    {
        $optionName = $this->getName();

        return
            ($optionValue !== '' && ((isset($queryParams[$optionName]) && $queryParams[$optionName] === $optionValue))) ||
            ($optionValue === '' && (!isset($queryParams[$optionName]) || $queryParams[$optionName] === ''));
    }


    /**
     * @param string $label
     * @return array
     */
    static public function valueOptionUnknown(string $label = "(Inconnu.e)"): array
    {
        return ['value' => self::VALUE_NULL, 'label' => $label];
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
<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

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

    /**
     * @var bool
     */
    protected bool $allowsEmptyOption = true;

    /**
     * @var string
     */
    protected string $emptyOptionLabel = "(Peu importe)";

    /**
     * @var bool
     */
    protected bool $allowsNoneOption = false;

    /**
     * @var string
     */
    protected string $noneOptionLabel = "(Non renseigné)";

    /**
     * @param string $label
     * @param string $name
     * @param array $attributes
     * @param string|null $defaultValue
     */
    public function __construct(string $label, string $name, array $attributes = [], string $defaultValue = null)
    {
        parent::__construct($label, $name);

        $this
            ->setAttributes($attributes)
            ->setDefaultValue($defaultValue);
    }

    /**
     * @param SearchFilterValueInterface|null $defaultValue
     * @return self
     */
    public function setDefaultValueAsObject(SearchFilterValueInterface $defaultValue = null): self
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
    public function setDataProvider(callable $dataProvider): self
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }
    /**
     * @return callable
     */
    public function getDataProvider(): ?callable
    {
        return $this->dataProvider;
    }

    /**
     * Application de ce filtre au query builder spécifié, en utilisant le champ de condition.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    protected function applyToQueryBuilderUsingWhereField(QueryBuilder $qb)
    {
        if ($this->getValue() === self::VALUE_NULL) {
            $qb->andWhere(sprintf("%s IS NULL", $this->whereField));
        } else {
            parent::applyToQueryBuilderUsingWhereField($qb);
        }
    }

    /**
     * Application par défaut de ce filtre au query builder spécifié, avec construction automatique du where.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    protected function applyToQueryBuilderByDefault(QueryBuilder $qb)
    {
        if ($this->getValue() === self::VALUE_NULL) {
            $alias = current($qb->getRootAliases());
            $qb->andWhere(sprintf("%s.%s IS NULL", $alias, $this->getName()));
        } else {
            parent::applyToQueryBuilderByDefault($qb);
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
        if ($this->allowsEmptyOption()) {
            $options[] = static::valueOptionEmpty($this->getEmptyOptionLabel());
        }
        if ($this->allowsNoneOption()) {
            $options[] = static::valueOptionUnknown($this->getNoneOptionLabel());
        }
        foreach ($data as $key => $value) {
            if ($value instanceof SearchFilterValueInterface) {
                $options[] = $value->createSearchFilterValueOption();
            } else {
                $options[] = static::valueOption((string) $value, (string) $key);
            }
        }

        return $options;
    }

    /**
     * @return self
     */
    public function init(): SearchFilter
    {
        // Si ce filtre n'autorise pas la sélection de l'option "vide", alors sélection de la 1ere valeur.
        if (!$this->allowsEmptyOption() && is_array($this->data) && !empty($this->data)) {
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
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Retourne les 'value_options' pour ce filtre, utilisables pour transmettre au Select d'un formulaire.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Spécifie les 'value_options' pour ce filtre, utilisables pour transmettre au Select d'un formulaire.
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retourne true si ce filtre autorise la sélection de l'option "vide".
     *
     * @return bool
     */
    public function allowsEmptyOption(): bool
    {
        return $this->allowsEmptyOption;
    }

    /**
     * Spécifie si ce filtre autorise la sélection de l'option "vide".
     *
     * @param bool $allowsEmptyOption
     * @return self
     */
    public function setAllowsEmptyOption(bool $allowsEmptyOption = true): self
    {
        $this->allowsEmptyOption = $allowsEmptyOption;
        return $this;
    }

    /**
     * Retourne le libellé de l'option "vide", ex: "Toutes" ou "Peu importe".
     *
     * @return string
     */
    public function getEmptyOptionLabel(): string
    {
        return $this->emptyOptionLabel;
    }

    /**
     * Spécifie le libellé de l'option "vide", ex: "Toutes" ou "Peu importe".
     *
     * @param string $emptyOptionLabel
     * @return self
     */
    public function setEmptyOptionLabel(string $emptyOptionLabel): self
    {
        $this->emptyOptionLabel = $emptyOptionLabel;

        return $this;
    }

    /**
     * Retourne true si ce filtre autorise la sélection de l'option "non renseigné".
     *
     * @return bool
     */
    public function allowsNoneOption(): bool
    {
        return $this->allowsNoneOption;
    }

    /**
     * Spécifie si ce filtre autorise la sélection de l'option "non renseigné".
     *
     * @param bool $allowsNoneOption
     * @return self
     */
    public function setAllowsNoneOption(bool $allowsNoneOption = true): self
    {
        $this->allowsNoneOption = $allowsNoneOption;
        return $this;
    }

    /**
     * Retourne le libellé de l'option "non renseigné", ex: "Non renseigné".
     *
     * @return string
     */
    public function getNoneOptionLabel(): string
    {
        return $this->noneOptionLabel;
    }

    /**
     * Spécifie le libellé de l'option "non renseigné", ex: "Non renseigné".
     *
     * @param string $noneOptionLabel
     * @return self
     */
    public function setNoneOptionLabel(string $noneOptionLabel): self
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
    public function isSelectOptionActive($optionValue, array $queryParams): bool
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
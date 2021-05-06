<?php

namespace Application\Search\Filter;

/**
 * Représente un filtre de type liste déroulante.
 *
 * @author Unicaen
 */
class SelectSearchFilter extends SearchFilter
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string[]
     */
    protected $options;

    /**
     * @var bool
     */
    protected $allowsEmptyOption = true;

    /**
     * @var string
     */
    protected $emptyOptionLabel = "(Peu importe)";

    /**
     * SelectFilter constructor.
     *
     * @param string $label
     * @param string $name
     * @param array $options
     * @param array $attributes
     * @param string $defaultValue
     */
    public function __construct(string $label, string $name, array $options, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name);

        $this
            ->setOptions($options)
            ->setAttributes($attributes)
            ->setDefaultValue($defaultValue);
    }

    /**
     * Génére à partir des valeurs spécifiées les 'value_options'
     * permettant de peupler la liste déroulante correspondant à ce filtre.
     *
     * Par défaut, s'attend à recevoir un tableau de valeurs scalaires.
     *
     * @param array $data
     * @return array
     */
    public function createValueOptionsFromData(array $data): array
    {
        $options = [];
        $options[] = static::valueOptionEmpty();
        foreach ($data as $value) {
            $options[] = static::valueOptionScalar($value);
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
    public function setAllowsEmptyOption(bool $allowsEmptyOption): self
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
     * @return SelectSearchFilter
     */
    public function setEmptyOptionLabel(string $emptyOptionLabel): self
    {
        $this->emptyOptionLabel = $emptyOptionLabel;

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
    static public function valueOptionUnknown($label = "(Inconnu.e)"): array
    {
        return ['value' => 'NULL', 'label' => $label];
    }

    /**
     * @param string $label
     * @return array
     */
    static public function valueOptionEmpty($label = "(Peu importe)"): array
    {
        return ['value' => '', 'label' => $label];
    }

    /**
     * @param object $entity
     * @param string|callable $getterOrCallableForLabel
     * @param string|callable $getterOrCallableForValue
     * @return array
     */
    static public function valueOptionEntity(object $entity, $getterOrCallableForLabel = '__toString', $getterOrCallableForValue = 'getId'): array
    {
        $label = is_callable($getterOrCallableForLabel) ? $getterOrCallableForLabel($entity) : $entity->$getterOrCallableForLabel();
        $value = is_callable($getterOrCallableForValue) ? $getterOrCallableForValue($entity) : $entity->$getterOrCallableForValue();

        return ['value' => (string) $value, 'label' => $label];
    }

    /**
     * @param mixed $scalar
     * @return array
     */
    static public function valueOptionScalar($scalar): array
    {
        return ['value' => $scalar, 'label' => $scalar];
    }
}
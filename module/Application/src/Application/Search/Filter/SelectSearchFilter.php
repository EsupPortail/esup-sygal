<?php

namespace Application\Search\Filter;

/**
 * Représente un filtre, de type liste déroulante.
 *
 * @author Unicaen
 */
class SelectSearchFilter extends SearchFilter
{
    /**
     * @var string[]
     */
    private $options;

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
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyOptionLabel(): string
    {
        return $this->emptyOptionLabel;
    }

    /**
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
}
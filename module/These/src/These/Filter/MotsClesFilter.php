<?php

namespace These\Filter;

use These\Entity\Db\MetadonneeThese;
use Laminas\Filter\AbstractFilter;
use LogicException;

/**
 * Filtre pour stockage des mots-clés de description d'une thèse.
 *
 * @package Application\Filter
 */
class MotsClesFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private string $separator = MetadonneeThese::SEPARATEUR_MOTS_CLES;

    /**
     * @param array $options Ex: ['separator' => '*']
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param string $separator
     * @return self
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param  string[]|string $motsCles
     * @return string
     */
    public function filter($motsCles)
    {
        if (! $motsCles) {
            return $motsCles;
        }

        $cleaner = function($value) {
            return trim($value, ' ' . $this->separator);
        };

        if (is_string($motsCles)) {
            $motsCles = explode($this->separator, $cleaner($motsCles));
        }
        elseif (! is_array($motsCles)) {
            throw new LogicException("Cas inattendu!");
        }

        return implode(" $this->separator ", array_map($cleaner, $motsCles));
    }
}
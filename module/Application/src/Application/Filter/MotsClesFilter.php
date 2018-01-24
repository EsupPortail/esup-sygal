<?php

namespace Application\Filter;

use Application\Entity\Db\MetadonneeThese;
use Zend\Filter\AbstractFilter;

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
    private $separator = MetadonneeThese::SEPARATEUR_MOTS_CLES;

    /**
     * MotsClesFilter constructor.
     *
     * @param array $options Ex: ['separator' => ';']
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param string $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  array|string $motsCles These ou collection d'objets Acteur
     * @return string
     */
    public function filter($motsCles)
    {
        if (! $motsCles) {
            return $motsCles;
        }

        $separator = $this->separator;
        $cleaner = function($value) use ($separator) {
            return trim($value, ' ' . $this->separator);
        };

        if (is_string($motsCles)) {
            $motsCles = explode($this->separator, $cleaner($motsCles));
        }
        elseif (! is_array($motsCles)) {
            throw new \LogicException("Cas inattendu!");
        }

        $motsCles = implode(" $this->separator ", array_map($cleaner, $motsCles));

        return $motsCles;
    }
}
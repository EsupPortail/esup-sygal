<?php

namespace Application\Filter;

use Laminas\Escaper\Escaper;
use Laminas\Filter\AbstractFilter;

class MotsClesFormatter extends AbstractFilter
{
    /**
     * @var string
     */
    private $separator = ';';

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
     * MotsClesFormatter constructor.
     *
     * @param array $options Exemple: ['separator' => ';']
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param array|string $motsCles
     * @param string $separator
     * @return mixed
     */
    static public function format($motsCles, $separator = ';')
    {
        $f = new static();
        $f->setSeparator($separator);

        return $f->filter($motsCles);
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
            return '';
        }
        
        if (is_string($motsCles)) {
            $motsCles = explode($this->separator, $motsCles);
        }
        elseif (! is_array($motsCles)) {
            throw new \LogicException("Cas inattendu!");
        }

        $escaper = new Escaper();

        $motsCles = array_map(function($motCle) use ($escaper) {
            return sprintf('<span class="mots-cles">%s</span>', $escaper->escapeHtml(trim($motCle)));
        }, $motsCles);

        return implode(' ', $motsCles);
    }
}
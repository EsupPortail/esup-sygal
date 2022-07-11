<?php

namespace Application\Filter;

use Application\Entity\Db\MetadonneeThese;
use Laminas\Escaper\Escaper;
use Laminas\Filter\AbstractFilter;
use LogicException;

class MotsClesFormatter extends AbstractFilter
{
    /**
     * @var string
     */
    private string $separator = MetadonneeThese::SEPARATEUR_MOTS_CLES;

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
     * @param array $options Exemple: ['separator' => '*']
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param string[]|string $motsCles
     * @param string $separator
     * @return string
     */
    static public function format($motsCles, string $separator = '*'): string
    {
        $f = new static();
        $f->setSeparator($separator);

        return $f->filter($motsCles);
    }

    /**
     * @param  string[]|string $motsCles
     * @return string
     */
    public function filter($motsCles): string
    {
        if (! $motsCles) {
            return '';
        }
        
        if (is_string($motsCles)) {
            $motsCles = explode($this->separator, $motsCles);
        }
        elseif (! is_array($motsCles)) {
            throw new LogicException("Cas inattendu!");
        }

        $escaper = new Escaper();

        $motsCles = array_map(function($motCle) use ($escaper) {
            return sprintf('<span class="mots-cles">%s</span>', $escaper->escapeHtml(trim($motCle)));
        }, $motsCles);

        return implode(' ', $motsCles);
    }
}
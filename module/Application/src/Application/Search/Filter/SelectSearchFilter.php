<?php

namespace Application\Search\Filter;

use Application\Search\Filter\SearchFilter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

/**
 * Représente un filtre de thèses, de type liste déroulante.
 *
 * @author Unicaen
 */
class SelectSearchFilter extends SearchFilter
{
    /*const NAME_etablissement = 'etablissement';
    const NAME_etatThese = 'etatThese';
    const NAME_ecoleDoctorale = 'ecoleDoctorale';
    const NAME_uniteRecherche = 'uniteRecherche';
    const NAME_anneePremiereInscription = 'anneePremiereInscription';
    const NAME_anneeUniv1ereInscription = 'anneeUniv1ereInscription';
    const NAME_anneeUnivInscription = 'anneeUnivInscription';
    const NAME_anneeSoutenance = 'anneeSoutenance';
    const NAME_discipline = 'discipline';
    const NAME_domaineScientifique = 'domaineScientifique';
    const NAME_financement = 'financement';*/

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
    public function __construct($label, $name, array $options, array $attributes = [], $defaultValue = null)
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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyOptionLabel()
    {
        return $this->emptyOptionLabel;
    }

    /**
     * @param string $emptyOptionLabel
     * @return SelectSearchFilter
     */
    public function setEmptyOptionLabel($emptyOptionLabel)
    {
        $this->emptyOptionLabel = $emptyOptionLabel;

        return $this;
    }
}
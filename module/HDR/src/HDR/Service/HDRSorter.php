<?php

namespace HDR\Service;

use Application\QueryBuilder\DefaultQueryBuilder;
use Application\View\Helper\Sortable;
use UnicaenApp\Exception\LogicException;

/**
 *
 *
 * @author Unicaen
 */
class HDRSorter
{
    const NAME_etablissement = 'etablissement';
    const NAME_etatHDR = 'etatHDR';
    const NAME_candidat = 'candidat';
    const NAME_uniteRecherche = 'uniteRecherche';
    const NAME_dateSoutenance = 'date';

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string
     */
    private $direction = Sortable::ASC;

    /**
     * SelectFilter constructor.
     *
     * @param string $label
     * @param string $name
     */
    public function __construct($label, $name)
    {
        $this
            ->setLabel($label)
            ->setName($name);
    }

    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams)
    {
        $sortQueryParam = $this->paramFromQueryParams('sort', $queryParams) ? $queryParams['sort'] : null;
        if (! $sortQueryParam) {
            $this->setEnabled(false);
            return;
        }

        // NB: le tri ne peut porter que sur un seul attribut

        if ($sortQueryParam !== $this->getName()) {
            $this->setEnabled(false);
            return;
        }

        $direction = $this->paramFromQueryParams('direction', $queryParams) ?: Sortable::ASC;

        $this->setEnabled(true);
        $this->setDirection($direction);
    }

    /**
     * @param string $name
     * @param array  $queryParams
     * @return string
     */
    private function paramFromQueryParams($name, array $queryParams)
    {
        if (! array_key_exists($name, $queryParams)) {
            // null <=> paramètre absent
            return null;
        }

        // NB: '' <=> "Tous"

        return $queryParams[$name];
    }

    /**
     * @param DefaultQueryBuilder $qb
     */
    public function applyToQueryBuilder(DefaultQueryBuilder $qb)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $name = $this->getName();
        $direction = $this->getDirection();

        switch ($name) {
            case self::NAME_etablissement:
                $qb
                    ->leftJoin('t.etablissement', 'e_sort')
                    ->leftJoin('e_sort.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $direction);
                break;

            case self::NAME_etatHDR:
                $qb
                    ->addOrderBy('t.etatHDR', $direction);
                break;

            case self::NAME_candidat:
                $qb
                    ->addOrderBy('di.nomUsuel', $direction)
                    ->addOrderBy('di.prenom1', $direction);
                break;

            case self::NAME_uniteRecherche:
                $qb
                    ->leftJoin('t.uniteRecherche', 'ur_sort')
                    ->addOrderBy('ur_sort.sourceCode', $direction);
                break;

            case self::NAME_dateSoutenance:
                $qb
                    ->leftJoin('t.propositionsHDR', 'prop')
                    ->addOrderBy('prop.date', $direction);
                break;

            default:
                throw new LogicException("Cas inattendu : " . $name);
                break;
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return self
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }
}

<?php

namespace Application\Service\These;

use Application\View\Helper\Sortable;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\LogicException;

/**
 *
 *
 * @author Unicaen
 */
class TheseSorter
{
    const NAME_titre = 'titre';
    const NAME_etablissement = 'etablissement';
    const NAME_etatThese = 'etatThese';
    const NAME_numeroEtudiant = 'numeroEtudiant';
    const NAME_doctorant = 'doctorant';
    const NAME_ecoleDoctorale = 'ecoleDoctorale';
    const NAME_uniteRecherche = 'uniteRecherche';
    const NAME_datePremiereInscription = 'datePremiereInscription';
    const NAME_dateSoutenance = 'dateSoutenance';

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
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        if (! $this->isEnabled()) {
            return;
        }

        $name = $this->getName();
        $direction = $this->getDirection();

        switch ($name) {
            case self::NAME_titre:
                // trim et suppression des guillemets
                $orderBy = "TRIM(REPLACE($name, CHR(34), ''))"; // CHR(34) <=> "
                $qb->addOrderBy($orderBy, $direction);
                break;

            case self::NAME_etablissement:
                $qb
                    ->leftJoin('t.etablissement', 'e_sort')
                    ->leftJoin('e_sort.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $direction);
                break;

            case self::NAME_etatThese:
                $qb
                    ->addOrderBy('t.etatThese', $direction);
                break;

            case self::NAME_numeroEtudiant:
                $qb
                    ->addOrderBy('th.sourceCode', $direction);
                break;

            case self::NAME_doctorant:
                $qb
                    ->addOrderBy('di.nomUsuel', $direction)
                    ->addOrderBy('di.prenom1', $direction);
                break;

            case self::NAME_ecoleDoctorale:
                $qb
                    ->leftJoin('t.ecoleDoctorale', 'ed_sort')
                    ->addOrderBy('ed_sort.sourceCode', $direction);
                break;

            case self::NAME_uniteRecherche:
                $qb
                    ->leftJoin('t.uniteRecherche', 'ur_sort')
                    ->addOrderBy('ur_sort.sourceCode', $direction);
                break;

            case self::NAME_datePremiereInscription:
                $qb
                    ->addOrderBy('t.datePremiereInscription', $direction);
                break;

            case self::NAME_dateSoutenance:
                $qb
                    ->addOrderBy('t.dateSoutenance', $direction);
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

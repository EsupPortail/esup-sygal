<?php

namespace Application\Service\These\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\LogicException;

/**
 * Représente un filtre de thèses, de type liste déroulante.
 *
 * @author Unicaen
 */
class TheseSelectFilter extends TheseFilter
{
    const NAME_etablissement = 'etablissement';
    const NAME_etatThese = 'etatThese';
    const NAME_ecoleDoctorale = 'ecoleDoctorale';
    const NAME_uniteRecherche = 'uniteRecherche';
    const NAME_anneePremiereInscription = 'anneePremiereInscription';
    const NAME_discipline = 'discipline';

    /**
     * @var string[]
     */
    private $options;

    /**
     * SelectFilter constructor.
     *
     * @param string $label
     * @param string $name
     * @param array $options
     * @param array $attributes
     */
    public function __construct($label, $name, array $options, array $attributes = [])
    {
        parent::__construct($label, $name);

        $this
            ->setOptions($options)
            ->setAttributes($attributes);
    }

    /**
     * @param QueryBuilder      $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $filterValue = $this->getValue();
        if (! $filterValue) {
            return;
        }

        $name = $this->getName();

        switch ($name) {
            case self::NAME_etablissement:
                $qb
                    ->join('t.etablissement', 'e', Join::WITH, 'e.code = :etabCode')
                    ->setParameter('etabCode', $filterValue);
                break;

            case self::NAME_etatThese:
                $qb
                    ->andWhere('t.etatThese = :etat')->setParameter('etat', $filterValue);
                break;

            case self::NAME_ecoleDoctorale:
                if ($filterValue === 'NULL') {
                    $qb
                        ->andWhere('t.ecoleDoctorale IS NULL');
                } else {
                    $qb
                        ->join('t.ecoleDoctorale', 'ed', Join::WITH, 'ed.sourceCode = :edSourceCode')
                        ->setParameter('edSourceCode', $filterValue);
                }
                break;

            case self::NAME_uniteRecherche:
                if ($filterValue === 'NULL') {
                    $qb
                        ->andWhere('t.uniteRecherche IS NULL');
                } else {
                    $qb
                        ->join('t.uniteRecherche', 'ur', Join::WITH, 'ur.sourceCode = :urSourceCode')
                        ->setParameter('urSourceCode', $filterValue);
                }
                break;

            case self::NAME_anneePremiereInscription:
                if ($filterValue === 'NULL') {
                    $qb
                        ->andWhere('t.datePremiereInscription IS NULL');
                } else {
                    $qb
                        ->andWhere('year(t.datePremiereInscription) = :anneePremiereInscription')
                        ->setParameter('anneePremiereInscription', $filterValue);
                }
                break;

            case self::NAME_discipline:
                if ($filterValue === 'NULL') {
                    $qb
                        ->andWhere('t.libelleDiscipline IS NULL');
                } else {
                    $qb
                        ->andWhere('t.libelleDiscipline = :discipline')
                        ->setParameter('discipline', $filterValue);
                }
                break;

            default:
                throw new LogicException("Cas inattendu : " . $name);
                break;
        }
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
}
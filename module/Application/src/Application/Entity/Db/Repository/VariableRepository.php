<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Entity\Db\Variable;

class VariableRepository extends DefaultEntityRepository
{
    /**
     * @see VariableRepository::findByCodeAndEtab()
     *
     * @param string|string[] $code
     * @param These           $these
     * @return Variable|Variable[]|null
     */
    public function findByCodeAndThese($code, These $these)
    {
        return $this->findByCodeAndEtab(
            $code,
            $these->getEtablissement(),
            $these->getDateSoutenance() ?: $these->getDatePrevisionSoutenance());
    }

    /**
     * Recherche d'une ou plusieurs variables.
     *
     * Valeurs retournée :
     * - Si un seul code est spécifié: null ou Variable trouvée.
     * - Si pluieurs codes sont spécifiés: tableau [code => Variable].
     *
     * Une variable possède une période de validité. Seules les variables dont la période de validité
     * contient la date d'observation spécifiée sont retenues.
     *
     * Si plusieurs variables sont valides à la date d'observation spécifiée, c'est celle dont la date de
     * fin de validité est la plus tardive qui est retenue.
     *
     * @param string|string[] $code
     * @param Etablissement   $etab
     * @param \DateTime|null  $dateObservation
     * @return null|Variable|Variable[]
     */
    public function findByCodeAndEtab($code, Etablissement $etab, \DateTime $dateObservation = null)
    {
        $dateObservation = $dateObservation ?: new \DateTime('now');

        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->in('v.code', (array) $code))
            ->andWhere('v.etablissement = :etab')
            ->andWhere(':dateObservation BETWEEN v.dateDebutValidite AND v.dateFinValidite')
            ->orderBy('v.dateFinValidite', 'ASC') // tri chronologique important!
            ->setParameter('etab', $etab)
            ->setParameter('dateObservation', $dateObservation)
            ;

        /** @var Variable[] $results */
        $results = $qb->getQuery()->getResult();

        if (! is_array($code)) {
            return current($results) ?: null;
        }

        $variables = [];
        foreach ($results as $v) {
            $variables[$v->getCode()] = $v;
        }

        return $variables;
    }
}
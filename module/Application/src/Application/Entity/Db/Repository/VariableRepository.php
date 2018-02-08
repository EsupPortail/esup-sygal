<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Variable;

/**
 *
 */
class VariableRepository extends DefaultEntityRepository
{
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
     * @param string|string[] $sourceCode
     * @param Etablissement   $etab
     * @param \DateTime|null  $dateObservation
     * @return null|Variable|Variable[]
     */
    public function findByCodeAndEtab($sourceCode, Etablissement $etab, \DateTime $dateObservation = null)
    {
        $dateObservation = $dateObservation ?: new \DateTime('now');

        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->in('v.sourceCode', (array) $sourceCode))
            ->andWhere('v.etablissement = :etab')
            ->andWhere(':dateObservation BETWEEN v.dateDebutValidite AND v.dateFinValidite')
            ->orderBy('v.dateFinValidite', 'ASC') // tri chronologique important!
            ->setParameter('etab', $etab)
            ->setParameter('dateObservation', $dateObservation);

        /** @var Variable[] $results */
        $results = $qb->getQuery()->getResult();

        if (! is_array($sourceCode)) {
            return current($results) ?: null;
        }

        $variables = [];
        foreach ($results as $v) {
            $variables[$v->getSourceCode()] = $v;
        }

        return $variables;
    }

    /**
     * Retourne la valeur d'une variable.
     *
     * Chaque variable possède une période de validité. Seules les variables dont la période de validité
     * contient la date d'observation spécifiée sont retournées.
     *
     * Si plusieurs variables sont valides à la date d'observation spécifiée, c'est celle dont la date de
     * fin de validité est la plus tardive qui est retournée.
     *
     * @param string         $sourceCode Code unique de la variable
     * @param \DateTime|null $dateObservation Date d'observation ("now" si absent)
     * @return string|null
     * @deprecated Utiliser findByCodeAndEtab()
     */
    public function valeur($sourceCode, \DateTime $dateObservation = null)
    {
    }
}
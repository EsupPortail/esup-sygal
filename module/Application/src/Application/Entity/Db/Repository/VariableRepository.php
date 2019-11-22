<?php

namespace Application\Entity\Db\Repository;

use Application\Constants;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Entity\Db\Variable;
use UnicaenApp\Exception\RuntimeException;

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
        $dateObservation = $these->getDateSoutenance() ?: $these->getDatePrevisionSoutenance();

        return $this->findByCodeAndEtab(
            $code,
            $these->getEtablissement(),
            $dateObservation ?: new \DateTime());
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
        $dateObservation = $dateObservation ?: date_create('now');

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
            $variable =  current($results) ?: null;
            if ($variable === null) {
                throw new RuntimeException(sprintf(
                    "La valeur de la variable '%s' est manquante pour l'établissement '%s' " .
                    "à la date d'observation %s.",
                    $code, $etab->getCode(), $dateObservation->format(Constants::DATE_FORMAT)
                ));
            }
            return $variable;
        }

        $variables = [];
        foreach ($results as $v) {
            $variables[$v->getCode()] = $v;
        }
        foreach ($code as $c) {
            if (!isset($variables[$c])) {
                throw new RuntimeException(sprintf(
                    "La valeur de la variable '%s' est manquante pour l'établissement '%s' " .
                    "à la date d'observation %s.",
                    $c, $etab->getCode(), $dateObservation->format(Constants::DATE_FORMAT)
                ));
            }
        }

        return $variables;
    }
}
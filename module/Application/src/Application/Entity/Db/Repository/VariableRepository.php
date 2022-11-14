<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Variable;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class VariableRepository extends DefaultEntityRepository
{
    /**
     * Recherche une variable par son code et pour l'établissement de rattachement d'une thèse.
     *
     * @param string $code
     * @param These $these
     * @return Variable|null
     *
     * @see VariableRepository::findOneByCodeAndEtab()
     */
    public function findOneByCodeAndThese(string $code, These $these): ?Variable
    {
        $dateObservation = $these->getDateSoutenance() ?: $these->getDatePrevisionSoutenance();

        return $this->findOneByCodeAndEtab(
            $code,
            $these->getEtablissement(),
            $dateObservation ?: new DateTime());
    }

    /**
     * Recherche une variable par son code et l'établissement concerné.
     *
     * Une variable possède une période de validité. Seules les variables dont la période de validité
     * contient la date d'observation spécifiée sont retenues.
     *
     * Si plusieurs variables sont valides à la date d'observation spécifiée, c'est celle dont la date de
     * fin de validité est la plus tardive qui est retenue.
     *
     * @param string $code
     * @param Etablissement $etab
     * @param \DateTime|null $dateObservation
     * @return null|Variable
     */
    public function findOneByCodeAndEtab(string $code, Etablissement $etab, DateTime $dateObservation = null): ?Variable
    {
        $dateObservation = $dateObservation ?: date_create();

        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->in('v.code', (array) $code))
            ->andWhere('v.etablissement = :etab')
            ->andWhere(':dateObservation BETWEEN v.dateDebutValidite AND v.dateFinValidite')
            ->orderBy('v.dateFinValidite', 'desc') // tri important!
            ->setParameter('etab', $etab)
            ->setParameter('dateObservation', $dateObservation)
        ;

        /** @var Variable|null $variable */
        try {
            $variable = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : plusieurs Variables trouvées");
        }

//        if ($variable === null) {
//            throw new RuntimeException(sprintf(
//                "La valeur de la variable '%s' est manquante pour l'établissement '%s' " .
//                "à la date d'observation %s.",
//                $code, $etab->getStructure()->getCode(), $dateObservation->format(Constants::DATE_FORMAT)
//            ));
//        }

        return $variable;
    }

    /**
     * Recherche de variables par le code, pour tous les établissements.
     *
     * Une variable possède une période de validité. Seules les variables dont la période de validité
     * contient la date d'observation spécifiée sont retenues.
     *
     * Si plusieurs variables sont valides à la date d'observation spécifiée, c'est celle dont la date de
     * fin de validité est la plus tardive qui est retenue.
     *
     * @param string $code
     * @param \DateTime|null $dateObservation
     * @return Variable[]
     */
    public function findByCode(string $code, DateTime $dateObservation = null): array
    {
        $dateObservation = $dateObservation ?: date_create();

        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->in('v.code', (array) $code))
            ->andWhere(':dateObservation BETWEEN v.dateDebutValidite AND v.dateFinValidite')
            ->orderBy('v.dateFinValidite', 'desc') // tri important!
            ->setParameter('dateObservation', $dateObservation)
        ;

        return $qb->getQuery()->getResult();
    }
}
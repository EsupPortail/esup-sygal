<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class ActeurRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCodeIndividu
     * @return Acteur[]
     */
    public function findBySourceCodeIndividu($sourceCodeIndividu)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('r')
            ->join('a.individu', 'i', Join::WITH, 'i.sourceCode like :sourceCode')
            ->join('a.role', 'r')
            ->setParameter('sourceCode', '%::' . $sourceCodeIndividu);

        return $qb->getQuery()->getResult();
    }

    public function findActeurByIndividu($individuId)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise()
            ->andWhere('a.individu = :individu')
            ->setParameter('individu', $individuId)
            ->orderBy('a.id', 'DESC')
        ;

        $acteurs = $qb->getQuery()->getResult();
        return current($acteurs);
    }

    /**
     * Recherche d'individu, en SQL pure.
     *
     * @param string  $text
     * @param string  $type (doctorant, acteur, ...)
     * @param integer $limit
     *
     * @return array
     */
    public function findByText($text, $type = null, $limit = 100)
    {
        if (strlen($text) < 2) return [];

        $subsql = "SELECT MAX(a.ID) FROM ACTEUR a JOIN INDIVIDU i ON a.INDIVIDU_ID = i.ID WHERE rownum <= 100 AND LOWER(i.NOM_USUEL) LIKE CONCAT(LOWER('".$text."'),'%') GROUP BY a.INDIVIDU_ID";
        $sql    = "SELECT * FROM ACTEUR a JOIN INDIVIDU i ON a.INDIVIDU_ID = i.ID WHERE a.ID IN (" . $subsql. ")";

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rencontrée dans la requête de recherche d'individu", null, $e);
        }

        return $stmt->fetchAll();
    }
}
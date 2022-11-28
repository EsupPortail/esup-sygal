<?php

namespace These\Service\TheseAnneeUniv;

use Application\Entity\AnneeUniv;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\TheseAnneeUniv;

class TheseAnneeUnivService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(TheseAnneeUniv::class);
    }

    /**
     * @param Etablissement|null $etablissement
     * @param bool $cacheable
     * @return int[]
     */
    public function fetchDistinctAnneesUniv1ereInscription(Etablissement $etablissement = null, $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("t.anneeUniv")
            ->orderBy("t.anneeUniv");

        if ($etablissement !== null) {
            $qb
                ->join('t.these', 'th')
                ->join('th.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $qb->setCacheable($cacheable);

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    /**
     * Retourne l'année universitaire courante.
     *
     * @return AnneeUniv
     */
    public function anneeUnivCourante(): AnneeUniv
    {
        return AnneeUniv::courante();
    }

    /**
     * Retourne l'année universitaire précédent celle en cours.
     *
     * @return AnneeUniv
     */
    public function anneeUnivPrecedente(): AnneeUniv
    {
        return AnneeUniv::precedente();
    }
}
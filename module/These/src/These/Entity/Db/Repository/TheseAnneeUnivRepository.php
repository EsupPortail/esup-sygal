<?php

namespace These\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;

class TheseAnneeUnivRepository extends DefaultEntityRepository
{
    public function findOneTheseAnneeUnivForTheseAndAnnee(These $these, int $premiereAnnee): ?TheseAnneeUniv
    {
        $qb = $this->createQueryBuilder('tau')
            ->join('tau.these', 't')->addSelect('t')
            ->leftJoin('tau.inscriptionAdministrative', 'ia')->addSelect('ia')
            ->andWhere('t = :these')->setParameter('these', $these)
            ->andWhere('tau.anneeUniv = :anneeUniv')->setParameter('anneeUniv', $premiereAnnee)
            ->andWhereNotHistorise('tau');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                "Anomalie rencontrée : plusieurs TheseAnneeUniv trouvées pour la thèse $these et l'année $premiereAnnee",
                null,
                $e
            );
        }
    }
}
<?php

namespace Doctorant\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\MissionEnseignement;

class MissionEnseignementRepository extends DefaultEntityRepository
{

    /** @return MissionEnseignement[] */
    public function findByDoctorant(Doctorant $doctorant) : array
    {
        $qb = $this->createQueryBuilder('mission');
        $qb
            ->join('mission.doctorant', 'doctorant')->addSelect('doctorant')
            ->where('mission.doctorant = :doctorant')->setParameter('doctorant', $doctorant)
            ->andWhere('mission.histoDestruction is null')
        ;
        return $qb->getQuery()->getResult();
    }
}
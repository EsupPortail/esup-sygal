<?php

namespace Soutenance\Service\IndividuSimulable;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndividuSimulableService {
    use EntityManagerAwareTrait;


    /**
     * @param $term
     * @return Individu[]
     */
    public function searchForIndividuSimulable($term) {
        $qb = $this->getEntityManager()->getRepository(Utilisateur::class)->createQueryBuilder('utilisateur')
            ->addSelect('individu')->join('utilisateur.individu', 'individu')
            ->andWhere("CONCAT(individu.nomUsuel, ' ', individu.prenom1) LIKE :search")
            ->andWhere('individu.email IS NOT NULL')
            ->setParameter('search', '%'.$term.'%');
        /** @var Utilisateur[] $result */
        $result = $qb->getQuery()->getResult();

        $return = [];
        foreach ($result as $item) {
            $return[$item->getIndividu()->getId()] = $item->getIndividu();
        }
        return $return;
    }

    public function getIndividusSimulablesAsOptions() {
        $qb = $this->getEntityManager()->getRepository(Utilisateur::class)->createQueryBuilder('utilisateur')
            ->addSelect('individu')->join('utilisateur.individu', 'individu')
            ->andWhere('individu.email IS NOT NULL')
            ->orderBy('individu.nomUsuel, individu.prenom1', 'ASC')
        ;
        $result = $qb->getQuery()->getResult();

        $return = [];
        /** @var Utilisateur[] $result */
        foreach ($result as $item) {
            $return[$item->getIndividu()->getId()] = $item->getIndividu()->getNomComplet();
        }
        return $return;
    }
}
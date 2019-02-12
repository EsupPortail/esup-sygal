<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;

class UtilisateurRepository extends DefaultEntityRepository
{
    /**
     * Recherche les utilisateurs lié à un individu.
     *
     * @param Individu $individu
     * @return Utilisateur[]
     */
    public function findByIndividu(Individu $individu)
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.individu', 'i')
            ->where('i = :individu')
            ->setParameter('individu', $individu);

        /** @var Utilisateur[] $utilisateurs */
        $utilisateurs = $qb->getQuery()->getResult();

        return $utilisateurs;
    }
}
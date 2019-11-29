<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

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

    /**
     * @param string $username
     * @return Utilisateur
     */
    public function findByUsername($username)
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Utilisateur partagent le même username [".$username."]", 0 , $e);
        }
        return $result;
    }

    /**
     * @param string $token
     * @return Utilisateur
     */
    public function findByToken($token)
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.passwordResetToken = :token')
            ->setParameter('token', $token);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Utilisateur partagent le même token [".$token."]", 0 , $e);
        }
        return $result;
    }
}
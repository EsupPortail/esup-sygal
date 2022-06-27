<?php

namespace Application\Entity\Db\Repository;

use Individu\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class UtilisateurRepository extends DefaultEntityRepository
{
    /**
     * Recherche les utilisateurs lié à un individu.
     *
     * @param Individu  $individu
     * @param bool|null $isLocal
     * @return Utilisateur[]
     */
    public function findByIndividu(Individu $individu, $isLocal = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.individu', 'i')
            ->where('i = :individu')
            ->setParameter('individu', $individu);

        if ($isLocal !== null) {
            if ($isLocal) {
                $qb->andWhere($qb->expr()->notIn('u.password', ['shib', 'ldap']));
            } else {
                $qb->andWhere($qb->expr()->in('u.password', ['shib', 'ldap']));
            }
        }

        $utilisateurs = $qb->getQuery()->getResult();

        if ($isLocal && count($utilisateurs) > 1) {
            throw new RuntimeException(
                "Plusieurs Utilisateur partagent le même individu " . $individu->getId() . " : " .
                implode(', ', array_map(function (Utilisateur $u) { return $u->getId(); }, $utilisateurs))
            );
        }

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
            $utilisateur = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Utilisateur partagent le même username [".$username."]", 0, $e);
        }

        return $utilisateur;
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
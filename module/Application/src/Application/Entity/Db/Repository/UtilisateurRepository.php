<?php

namespace Application\Entity\Db\Repository;

use Doctrine\DBAL\Exception;
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
     * Recherche textuelle d'utilisateurs, en SQL pur.
     *
     * @param string $text
     * @param int $limit
     *
     * @return int[] Ids des utilisateurs trouvés
     */
    public function findByText(string $text, int $limit = 100): array
    {
        if (strlen($text) < 2) return [];

        $pieces = explode(' ', $text);

        $sql =
            "select u.* " .
            "from utilisateur u " .
            "left join individu i on i.id = u.individu_id " .
            "left join individu_rech ir on ir.id = i.id ";

        $criteria = [
            'u.username',
            'u.email',
            'u.display_name',
            'ir.haystack',
        ];

        $sqlCri = [];
        foreach ($pieces as $p) {
            $sqlCri[$p] = [];
            foreach ($criteria as $c) {
                $sqlCri[$p][] = "$c like str_reduce($$%" . $p . "%$$)";
            }
            $sqlCri[$p] = '(' . implode(' or ', $sqlCri[$p]) . ')';
        }
        $sqlCri = implode(' and ', $sqlCri);

        $sql .= 'where (' . $sqlCri . ') ';
        if ($limit > 0) {
            $sql .= " limit " . $limit;
        }

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Erreur rencontrée lors de l'exécution de la requête de recherche", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche", null, $e);
        }
    }

    /**
     * Recherche un utilisateur par le token permettant de changer de mot de passe.
     */
    public function findByToken(string $token): ?Utilisateur
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
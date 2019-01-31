<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class UtilisateurRepository extends DefaultEntityRepository
{
    /**
     * @return Utilisateur
     */
    public function fetchAppPseudoUser()
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username = Utilisateur::APP_UTILISATEUR_USERNAME);

        try {
            /** @var Utilisateur $utilisateur */
            $utilisateur = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs pseudo-utilisateur trouvé avec ce username: " . $username);
        }

        return $utilisateur;
    }

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
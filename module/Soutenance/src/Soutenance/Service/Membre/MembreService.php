<?php

namespace Soutenance\Service\Membre;

//TODO faire le repo aussi
use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MembreService {
    use EntityManagerAwareTrait;

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->andWhere("membre.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [".$id."] ont été trouvées !");
        }
        return $result;
    }

    /**
     * @param Membre $membre
     */
    public function create($membre)
    {
        $this->getEntityManager()->persist($membre);
        try {
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'enregistrement d'un membre de jury !");
        }
    }

    /**
     * @param Membre $membre
     */
    public function update($membre) {
        try {
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour d'un membre de jury !");
        }
    }

    /**
     * @param Membre $membre
     */
    public function delete($membre) {
        $this->getEntityManager()->remove($membre);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'effacement d'un membre de jury !");
        }
    }

    /**
     * @param Proposition $proposition
     * @param Acteur $acteur
     */
    public function createMembre($proposition, $acteur) {
        //Qualité par défaut
        $autreA = $this->getQualiteById(3);

        $membre = new Membre();
        $membre->setProposition($proposition);
        $membre->setDenomination($acteur->getIndividu()->getNomComplet());
        $membre->setGenre(($acteur->getIndividu()->estUneFemme())?"F":"H");
        $qualite = $this->getQualiteByLibelle($acteur->getQualite());
        $membre->setQualite(($qualite)?$qualite:$autreA);
        $membre->setEtablissement($acteur->getEtablissement()->getLibelle());
        $membre->setRole(Membre::MEMBRE);
        $membre->setExterieur("non");
        $membre->setEmail($acteur->getIndividu()->getEmail());
        $membre->setIndividu($acteur->getIndividu());
        $membre->setVisio(false);
        $this->create($membre);
    }

    public function getQualiteById($id) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->andWhere("qualite.id = :id")
            ->setParameter("id", $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }

    public function getQualiteByLibelle($libelle) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->andWhere("qualite.libelle = :libelle")
            ->setParameter("libelle", $libelle);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }


    public function findAllQualites()
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder('qualite')
            ->orderBy('qualite.rang, qualite.libelle');
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function createQualite($qualite)
    {
        $this->getEntityManager()->persist($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'enregistrement en BD d'une nouvelle qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function updateQualite($qualite)
    {
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     */
    public function removeQualite($qualite)
    {
        $this->getEntityManager()->remove($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'effacement en BD d'une nouvelle qualité.");
        }
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteursByProposition($proposition)
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder('membre')
            ->andWhere('membre.proposition = :proposition')
            ->andWhere('membre.role = :rapporteur OR membre.role = :rapporteurAbsent')
            ->setParameter('proposition', $proposition)
            ->setParameter('rapporteur', Membre::RAPPORTEUR)
            ->setParameter('rapporteurAbsent', Membre::RAPPORTEUR_ABSENT)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}

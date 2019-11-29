<?php

namespace Soutenance\Service\Membre;

use Application\Entity\Db\Acteur;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\Qualite;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MembreService {
    use EntityManagerAwareTrait;
    use QualiteServiceAwareTrait;

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->addSelect('proposition')->join('membre.proposition', 'proposition')
            ->addSelect('qualite')->join('membre.qualite', 'qualite')
            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur')
            ;
        return $qb;
    }

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->createQueryBuilder()
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
        $inconnue = $this->getQualiteService()->getQualiteById(Qualite::ID_INCONNUE);

        $membre = new Membre();
        $membre->setProposition($proposition);
        $membre->setPrenom($acteur->getIndividu()->getPrenom());
        $membre->setNom($acteur->getIndividu()->getNomUsuel());
        $membre->setGenre(($acteur->getIndividu()->estUneFemme())?"F":"H");
        $qualite = $this->getQualiteService()->getQualiteByLibelle($acteur->getQualite());
        $membre->setQualite(($qualite)?$qualite:$inconnue);
        $membre->setEtablissement($acteur->getEtablissement()->getLibelle());
        $membre->setRole(Membre::MEMBRE_JURY);
        $membre->setExterieur("non");
        $membre->setEmail($acteur->getIndividu()->getEmail());
        $membre->setActeur($acteur);
        $membre->setVisio(false);
        $this->create($membre);
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteursByProposition($proposition)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.proposition = :proposition')
            ->andWhere('membre.role = :rapporteur OR membre.role = :rapporteurVisio or membre.role = :rapporteurAbsent')
            ->setParameter('proposition', $proposition)
            ->setParameter('rapporteur', Membre::RAPPORTEUR_JURY)
            ->setParameter('rapporteurVisio', Membre::RAPPORTEUR_VISIO)
            ->setParameter('rapporteurAbsent', Membre::RAPPORTEUR_ABSENT)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}

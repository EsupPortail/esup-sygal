<?php

namespace Soutenance\Service\Membre;

use Application\Entity\Db\Acteur;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Ramsey\Uuid\Uuid;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\Qualite;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class MembreService {
    use EntityManagerAwareTrait;
    use QualiteServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function create($membre)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $membre->setHistoCreateur($user);
        $membre->setHistoCreation($date);
        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);
        $membre->setClef($this->genererClef());

        try {
            $this->getEntityManager()->persist($membre);
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'enregistrement d'un membre de jury !");
        }
        return $membre;
    }

    /**
     * @param Membre $membre
     */
    public function update($membre) {

        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour d'un membre de jury !");
        }
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function historise($membre)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);
        $membre->setHistoDestructeur($user);
        $membre->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function restore($membre)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $membre->setHistoModificateur($user);
        $membre->setHistoModification($date);
        $membre->setHistoDestructeur(null);
        $membre->setHistoDestruction(null);

        try {
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $membre;
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

    /** REQUETES ******************************************************************************************************/

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
     * @return Membre
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
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Membre
     */
    public function getRequestedMembre($controller, $paramName = 'membre')
    {
        $id = $controller->params()->fromRoute($paramName);
        $membre = $this->find($id);
        return $membre;
    }

    /**
     * @param Proposition $proposition
     * @param Acteur $acteur
     */
    public function createMembre($proposition, $acteur) {
        //Qualité par défaut
        $inconnue = $this->getQualiteService()->getQualite(Qualite::ID_INCONNUE);

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


    /**
     * @param DateInterval $interval
     * @return array
     */
    public function getRapporteursEnRetard(DateInterval $interval)
    {
        try {
            $date = new DateTime();
            $date = $date->add($interval);
        } catch (Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la date.", 0, $e);
        }

        $qb = $this->createQueryBuilder()
            ->andWhere('membre.role = :rapporteurAbsent OR membre.role = :rapporteurVisio OR membre.role = :rapporteurJury')
            ->setParameter('rapporteurAbsent', Membre::RAPPORTEUR_ABSENT)
            ->setParameter('rapporteurVisio', Membre::RAPPORTEUR_VISIO)
            ->setParameter('rapporteurJury', Membre::RAPPORTEUR_JURY)
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->andWhere('etat.code = :EnCours')
            ->setParameter('EnCours', Etat::EN_COURS)
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('avis.id IS NULL')
            ->addSelect('these')->leftJoin('proposition.these', 'these')
            ->andWhere('these.dateSoutenance < :date')
//            ->andWhere('proposition.date < :date')
            ->setParameter('date', $date)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Acteur $acteur
     * @return Membre
     */
    public function getMembreByActeur(Acteur $acteur) {
        $qb = $this->createQueryBuilder()
            ->andWhere('membre.acteur = :acteur')
            ->setParameter('acteur', $acteur)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Membre partagent le même Acteur [".$acteur->getId()."]", 0, $e);
        }
        return $result;
    }

    public function createDummyMembre()
    {
        /** @var Proposition $proposition : la proposition est une porposition bidon  */
        $proposition = $this->getEntityManager()->getRepository(Proposition::class)->find(0);
        /** @var Qualite $qualite : la qualite 'Qualité inconnue' */
        $qualite = $this->getEntityManager()->getRepository(Qualite::class)->find(0);

        $membre = new Membre();
        $membre->setProposition($proposition);
        $membre->setGenre("-");
        $membre->setQualite($qualite);
        $membre->setEtablissement("FAUX ETABLISSEMENT");
        $membre->setRole(Membre::MEMBRE_JURY);
        $membre->setVisio(false);
        return $membre;
    }


    /**
     * @return string
     */
    public function genererClef() : string
    {
        $clef = "";
        for($i = 0 ; $i < 4; $i++) {
            if ($clef !== "") $clef .= "-";
            $clef .=  Uuid::uuid4()->toString();
        }
        return $clef;
    }

    /**
     * @param Membre $membre
     * @param string|null $clef
     * @return bool
     */
    public function verifierClef(Membre $membre, ?string $clef = '') : bool
    {
        return ($clef AND $membre->getClef() === $clef);
    }
}

<?php

namespace Soutenance\Service\Membre;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateInterval;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use LogicException;
use Soutenance\Entity\Etat;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Entity\Qualite;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenUtilisateur\Entity\Db\AbstractUser;
use UnicaenAuthToken\Entity\Db\AbstractUserToken;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;
use UnicaenAuthToken\Service\TokenServiceException;
use Laminas\Mvc\Controller\AbstractActionController;

class MembreService
{
    use EntityManagerAwareTrait;
    use QualiteServiceAwareTrait;
    use TokenServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function create(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->persist($membre);
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'enregistrement d'un membre de jury !");
        }
        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function update(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->flush($membre);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour d'un membre de jury !");
        }
        return $membre;
    }

    /**
     * @param Membre $membre
     * @return Membre
     */
    public function historise(Membre $membre) : Membre
    {
        try {
            $membre->historiser();
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
    public function restore(Membre $membre) : Membre
    {
        try {
            $membre->dehistoriser();
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
    public function delete(Membre $membre) : Membre
    {
        try {
            $this->getEntityManager()->remove($membre);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'effacement d'un membre de jury !");
        }
        return $membre;
    }

    /** REQUETES ******************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    private function createQueryBuilder() : QueryBuilder
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->addSelect('proposition')->join('membre.proposition', 'proposition')
            ->addSelect('qualite')->join('membre.qualite', 'qualite')
//            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur') // n'existe plus
//            ->addSelect('individu')->leftJoin('acteur.individu', 'individu')
            ;
        return $qb;
    }

    /**
     * @param int|null $id
     * @return Membre|null
     */
    public function find(?int $id) : ?Membre
    {
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
    public function getRequestedMembre(AbstractActionController $controller, string  $paramName = 'membre') : ?Membre
    {
        $id = $controller->params()->fromRoute($paramName);
        $membre = $this->find($id);
        return $membre;
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteursByProposition(Proposition $proposition) : array
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
    public function getRapporteursEnRetard(DateInterval $interval) : array
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
            ->setParameter('EnCours', Etat::EN_COURS_EXAMEN)
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
     * @param ActeurThese|ActeurHDR $acteur
     * @return Membre|null
     * @deprecated Equivaut à $acteur->getMembre() depuis quela relationa été inversée
     */
    public function getMembreByActeur(ActeurThese|ActeurHDR $acteur) : ?Membre
    {
//        $qb = $this->createQueryBuilder()
//            ->andWhere('membre.acteur = :acteur')
//            ->setParameter('acteur', $acteur)
//        ;
//
//        try {
//            $result = $qb->getQuery()->getOneOrNullResult();
//        } catch (NonUniqueResultException $e) {
//            throw new RuntimeException("Plusieurs Membre partagent le même Acteur [".$acteur->getId()."]", 0, $e);
//        }
//        return $result;
        return $acteur->getMembre();
    }

    /** FACADE ********************************************************************************************************/

    /**
     * @param Proposition $proposition
     * @param ActeurThese|ActeurHDR $acteur
     * @return Membre
     */
    public function createMembre(Proposition $proposition, ActeurThese|ActeurHDR $acteur) : Membre
    {
        $qualite = $this->qualiteService->getQualiteByLibelle($acteur->getLibelleQualite()) ?:
            $this->qualiteService->getQualiteParDefaut();

        $membre = new Membre();
        $membre->setProposition($proposition);
        $membre->setPrenom($acteur->getIndividu()->getPrenom());
        $membre->setNom($acteur->getIndividu()->getNomUsuel());

        //GERER CE CAS!!
        $membre->setGenre($acteur->getGenreFromIndividu());
        $membre->setQualite($qualite);
        $membre->setEtablissement(($acteur->getEtablissement())?$acteur->getEtablissement()->getStructure()->getLibelle():"Etablissement inconnu");
        $membre->setRole(Membre::MEMBRE_JURY);
        $membre->setExterieur("non");
        $membre->setEmail($acteur->getIndividu()->getEmailPro());
//        $membre->setActeur($acteur); // la relation a été inversée et celle dans le sens supprimée.
        $membre->setVisio(false);
        $this->create($membre);

        return $membre;
    }

    /**
     * @return Membre
     */
    public function createDummyMembre() : Membre
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

    public function generateUsername(Membre $membre) : string
    {
//        $acteur = $membre->getActeur();
        $proposition = $membre->getProposition();
        $acteur = $proposition instanceof PropositionThese ?
            $this->acteurTheseService->getRepository()->findActeurForSoutenanceMembre($membre) :
            $this->acteurHDRService->getRepository()->findActeurForSoutenanceMembre($membre);
        if ($acteur === null) throw new LogicException("La génération du username est basée sur l'Individu qui est mamquant.");
        $nomusuel = strtolower($acteur->getIndividu()->getNomUsuel());
        return ($nomusuel . "_" . $membre->getId());
    }

    /** RECUPERATION DE L'UTILSATEUR D'UN MEMBRE **********************************************************************/

    /**
     * @param Membre $membre
     * @return AbstractUser|null
     */
    public function getUtilisateur(Membre $membre) : ?AbstractUser
    {
//        $acteur = $membre->getActeur();
        $proposition = $membre->getProposition();
        $acteur = $proposition instanceof PropositionThese ?
            $this->acteurTheseService->getRepository()->findActeurForSoutenanceMembre($membre) :
            $this->acteurHDRService->getRepository()->findActeurForSoutenanceMembre($membre);
        if ($acteur === null) return null;
        $individu = $acteur->getIndividu();
        if ($individu === null) return null;

        /** @var AbstractUser[] $utilisateurs */
        $utilisateurs = $individu->getUtilisateurs();
        return (empty($utilisateurs))?null:$utilisateurs[0];
    }

    /** GESTION DES TOKENS ********************************************************************************************/

    public function retrieveToken(Membre $membre) : ?AbstractUserToken
    {
//        $individu = $membre->getActeur()->getIndividu();
        $proposition = $membre->getProposition();
        $acteur = $proposition instanceof PropositionThese ?
            $this->acteurTheseService->getRepository()->findActeurForSoutenanceMembre($membre) :
            $this->acteurHDRService->getRepository()->findActeurForSoutenanceMembre($membre);
        $individu = $acteur->getIndividu();
        $utilisateurs = $individu->getUtilisateurs();

        foreach ($utilisateurs as $utilisateur) {
            $token = $this->tokenService->findUserTokenByUserId($utilisateur->getId());
            if ($token !== null AND ! $token->isExpired()) return $token;
        }

        return null;
    }

    public function createToken(Membre $membre) : AbstractUserToken
    {
        $utilisateur = $this->getUtilisateur($membre);
        if ($utilisateur === null)
            throw new LogicException("Aucun utilisateur n'est correctement déclaré pour le membre $utilisateur");

        try {
            $userToken = $this->tokenService->createUserToken();
            $userToken->setUser($utilisateur);
            $userToken->setExpiredOn($membre->getProposition()->getDate());
            $this->tokenService->saveUserToken($userToken);
        } catch (TokenServiceException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la création du jeton", null, $e);
        }

        return $userToken;
    }

    public function retrieveOrCreateToken(Membre $membre) : AbstractUserToken
    {
        $token = $this->retrieveToken($membre);
        if ($token !== null) return $token;

        $token = $this->createToken($membre);
        return $token;
    }
}

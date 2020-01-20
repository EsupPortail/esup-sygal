<?php

namespace Soutenance\Service\Avis;

use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class AvisService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITÉS *******************************************************************************************/

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function create($avis)
    {
        /** @var Utilisateur $user */
        $user = $this->userContextService->getIdentityDb();
        /** @var DateTime $date */
        try {
            $date = new DateTime();
        } catch (Exception $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de la date");
        }
        $avis->setHistoCreation($date);
        $avis->setHistoCreateur($user);
        $avis->setHistoModification($date);
        $avis->setHistoModificateur($user);

        $this->getEntityManager()->persist($avis);
        try {
            $this->getEntityManager()->flush($avis);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de la création de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function update($avis)
    {
        /** @var Utilisateur $user */
        $user = $this->userContextService->getIdentityDb();
        /** @var DateTime $date */
        try {
            $date = new DateTime();
        } catch (Exception $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de la date");
        }
        $avis->setHistoModification($date);
        $avis->setHistoModificateur($user);

        try {
            $this->getEntityManager()->flush($avis);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de la mise à jour de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     */
    public function delete($avis)
    {
        $this->getEntityManager()->remove($avis);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de l\'effacement de l\'avis', $e);
        }
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function historiser($avis)
    {
        /** @var Utilisateur $user */
        $user = $this->userContextService->getIdentityDb();
        /** @var DateTime $date */
        try {
            $date = new DateTime();
        } catch (Exception $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de la date");
        }

        $avis->getValidation()->setHistoDestruction($date);
        $avis->getValidation()->setHistoDestructeur($user);
        $avis->getFichier()->setHistoDestruction($date);
        $avis->getFichier()->setHistoDestructeur($user);
        $avis->setHistoDestruction($date);
        $avis->setHistoDestructeur($user);

        try {
            $this->getEntityManager()->flush($avis->getValidation());
            $this->getEntityManager()->flush($avis->getFichier());
            $this->getEntityManager()->flush($avis);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'historisation de l'avis (id:".$avis->getId().").");
        }

        return $avis;
    }

    /** REQUETE *******************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(Avis::class)->createQueryBuilder('avis')
            ->addSelect('proposition')->join('avis.proposition', 'proposition')
            ->addSelect('membre')->join('avis.membre', 'membre')
        ;

        return $qb;
    }

    /**
     * @param int $id
     * @return Avis
     */
    public function getAvis($id)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('avis.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis partagent le même identifiant ['.$id.']', $e);
        }

        return $result;
    }

    /**
     * @param These these
     * @return Avis[]
     */
    public function getAvisByThese($these)
    {
        $qb =$this->createQueryBuilder()
            ->andWhere('1 = pasHistorise(avis)')
            ->andWhere('proposition.these = :these')
            ->setParameter('these', $these)
        ;
        $result = $qb->getQuery()->getResult();

        $avis = [];
        /** @var Avis $entry */
        foreach ($result as $entry) {
            $avis[$entry->getRapporteur()->getIndividu()->getId()] = $entry;
        }
        return $avis;
    }

    /**
     * @param Membre $membre
     * @return Avis
     */
    public function getAvisByMembre($membre)
    {
        if ($membre === null OR $membre->getActeur() === null) return null;
        $qb = $this->createQueryBuilder()
            ->andWhere('1 = pasHistorise(avis)')
            ->andWhere('avis.membre = :membre')
            ->setParameter('membre', $membre);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis sont associés au rapporteur ['.$membre->getId().' - '.$membre->getIndividu()->getNomComplet().']');
        }

        return $result;
    }
}
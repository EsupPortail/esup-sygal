<?php

namespace ComiteSuivi\Service\ComiteSuivi;

use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class ComiteSuiviService {
    use DateTimeTrait;
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param ComiteSuivi $comiteSuivi
     * @return ComiteSuivi
     */
    public function create(ComiteSuivi $comiteSuivi)
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $comiteSuivi->setHistoCreateur($user);
        $comiteSuivi->setHistoCreation($date);
        $comiteSuivi->setHistoModificateur($user);
        $comiteSuivi->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($comiteSuivi);
            $this->getEntityManager()->flush($comiteSuivi);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $comiteSuivi;
    }

    /**
     * @param ComiteSuivi $comiteSuivi
     * @return ComiteSuivi
     */
    public function update(ComiteSuivi $comiteSuivi)
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $comiteSuivi->setHistoModificateur($user);
        $comiteSuivi->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($comiteSuivi);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $comiteSuivi;
    }

    /**
     * @param ComiteSuivi $comiteSuivi
     * @return ComiteSuivi
     */
    public function historise(ComiteSuivi $comiteSuivi)
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $comiteSuivi->setHistoDestructeur($user);
        $comiteSuivi->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($comiteSuivi);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $comiteSuivi;
    }

    /**
     * @param ComiteSuivi $comiteSuivi
     * @return ComiteSuivi
     */
    public function restore(ComiteSuivi $comiteSuivi)
    {
        $comiteSuivi->setHistoDestructeur(null);
        $comiteSuivi->setHistoDestruction(null);

        try {
            $this->getEntityManager()->persist($comiteSuivi);
            $this->getEntityManager()->flush($comiteSuivi);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $comiteSuivi;
    }

    /**
     * @param ComiteSuivi $comiteSuivi
     * @return ComiteSuivi
     */
    public function delete(ComiteSuivi $comiteSuivi)
    {
        try {
            $this->getEntityManager()->remove($comiteSuivi);
            $this->getEntityManager()->flush($comiteSuivi);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.",0,$e);
        }

        return $comiteSuivi;
    }

    /** REQUETAGES ****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(ComiteSuivi::class)->createQueryBuilder('comite')
            ->addSelect('these')->join('comite.these', 'these')
        ;
        return $qb;
    }

    /**
     * @param string $champ
     * @param string $ordre
     * @return ComiteSuivi[]
     */
    public function getComitesSuivis($champ = 'these', $ordre = 'ASC')
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('comite.'.$champ, $ordre)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param $id
     * @return ComiteSuivi
     */
    public function getComiteSuivi($id)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('comite.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs ComiteSuivi partagent le même id [".$id."].", 0, $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return ComiteSuivi
     */
    public function getRequestedComiteSuivi($controller, $param='comite-suivi')
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getComiteSuivi($id);
        return $result;
    }

    /**
     * @param These $these
     * @param bool $historise
     * @return ComiteSuivi[]
     */
    public function getComitesSuivisByThese(These $these, $historise = false)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('comite.these = :these')
            ->setParameter('these', $these)
        ;

        if (! $historise) {
            $qb = $qb->andWhere('comite.histoDestruction IS NULL');
        }

        $result = $qb->getQuery()->getResult();
        return $result;
    }

}
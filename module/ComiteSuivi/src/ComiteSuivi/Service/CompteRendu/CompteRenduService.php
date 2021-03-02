<?php

namespace ComiteSuivi\Service\CompteRendu;

use Application\Service\UserContextServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Entity\Db\CompteRendu;
use ComiteSuivi\Entity\Db\Membre;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class CompteRenduService
{
    use EntityManagerAwareTrait;
    use DateTimeTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param CompteRendu $compteRendu
     * @return CompteRendu
     */
    public function create(CompteRendu $compteRendu) : CompteRendu
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $compteRendu->setHistoCreateur($user);
        $compteRendu->setHistoCreation($date);
        $compteRendu->setHistoModificateur($user);
        $compteRendu->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($compteRendu);
            $this->getEntityManager()->flush($compteRendu);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.", 0, $e);
        }

        return $compteRendu;
    }

    /**
     * @param CompteRendu $compteRendu
     * @return CompteRendu
     */
    public function update(CompteRendu $compteRendu) : CompteRendu
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $compteRendu->setHistoModificateur($user);
        $compteRendu->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($compteRendu);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.", 0, $e);
        }

        return $compteRendu;
    }

    /**
     * @param CompteRendu $compteRendu
     * @return CompteRendu
     */
    public function historise(CompteRendu $compteRendu) : CompteRendu
    {
        $date = $this->getDateTime();
        $user = $this->userContextService->getIdentityDb();
        $compteRendu->setHistoDestructeur($user);
        $compteRendu->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($compteRendu);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.", 0, $e);
        }

        return $compteRendu;
    }

    /**
     * @param CompteRendu $compteRendu
     * @return CompteRendu
     */
    public function restore(CompteRendu $compteRendu) : CompteRendu
    {
        $compteRendu->setHistoDestructeur(null);
        $compteRendu->setHistoDestruction(null);

        try {
            $this->getEntityManager()->persist($compteRendu);
            $this->getEntityManager()->flush($compteRendu);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.", 0, $e);
        }

        return $compteRendu;
    }

    /**
     * @param CompteRendu $compteRendu
     * @return CompteRendu
     */
    public function delete(CompteRendu $compteRendu) : CompteRendu
    {
        try {
            $this->getEntityManager()->remove($compteRendu);
            $this->getEntityManager()->flush($compteRendu);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'enregistrement en base.", 0, $e);
        }

        return $compteRendu;
    }

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(CompteRendu::class)->createQueryBuilder('compterendu')
            ->addSelect('comite')->join('compterendu.comite', 'comite')
            ->addSelect('membre')->join('compterendu.membre', 'membre')
        ;

        return $qb;
    }

    /**
     * @param $id
     * @return CompteRendu|null
     */
    public function getCompteRendu(int $id) : ?CompteRendu
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('compterendu.id = :id')
            ->setParameter('id', $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs CompteRendu partagent le même id [".$id."].", 0, $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return CompteRendu|null
     */
    public function getRequestedCompteRendu(AbstractActionController $controller, string $param='compte-rendu') : ?CompteRendu
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getCompteRendu($id);
        return $result;
    }

    /**
     * @param ComiteSuivi $comite
     * @param Membre $examinateur
     * @return CompteRendu|null
     */
    public function getCompteRenduByComiteAndExaminateur(ComiteSuivi $comite, Membre $examinateur) : ?CompteRendu
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('compterendu.comite = :comite')
            ->setParameter('comite', $comite)
            ->andWhere('compterendu.membre = :examinateur')
            ->setParameter('examinateur', $examinateur)
            ->andWhere('compterendu.histoDestruction IS NULL')
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs CompteRendu partagent le même comite [".$comite->getId()."]et le examinateur [".$examinateur->getId()."]", 0, $e);
        }
        return $result;
    }
}
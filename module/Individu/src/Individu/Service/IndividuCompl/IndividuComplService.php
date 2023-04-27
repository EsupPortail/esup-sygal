<?php

namespace Individu\Service\IndividuCompl;

use Application\Service\BaseService;
use Doctrine\ORM\ORMException;
use Individu\Entity\Db\IndividuCompl;
use Individu\Entity\Db\Repository\IndividuComplRepository;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class IndividuComplService extends BaseService
{
    /**
     * @return \Individu\Entity\Db\Repository\IndividuComplRepository
     */
    public function getRepository(): IndividuComplRepository
    {
        /** @var IndividuComplRepository $repo */
        $repo = $this->entityManager->getRepository(IndividuCompl::class);
        return $repo;
    }

    /** Entity managment **********************************************************************************************/

    public function create(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->entityManager->persist($individuCompl);
            $this->entityManager->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function update(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->entityManager->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function historise(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $individuCompl->historiser();
            $this->entityManager->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function restore(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $individuCompl->dehistoriser();
            $this->entityManager->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function delete(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->entityManager->remove($individuCompl);
            $this->entityManager->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    /** facade ********************************************************************************************************/

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return IndividuCompl|null
     */
    public function findRequestedIndividuCompl(AbstractActionController $controller, string $param="individu-compl") : ?IndividuCompl
    {
        $id = $controller->params()->fromRoute($param);
        /** @var IndividuCompl|null $individuCompl */
        $individuCompl = $this->getRepository()->find($id);
        return $individuCompl;
    }

    /**
     * Retourne les compléments d'individu ordonnés par nom d'individu.
     *
     * @return IndividuCompl[]
     */
    public function findComplements() : array
    {
        $qb = $this->getRepository()->createQueryBuilder('ic')
            ->addSelect('i')
            ->join('ic.individu', 'i')
            ->orderBy('i.nomUsuel');

        return $qb->getQuery()->getResult();
    }
}
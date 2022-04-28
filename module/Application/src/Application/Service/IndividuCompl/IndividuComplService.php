<?php

namespace Application\Service\IndividuCompl;

use Application\Entity\Db\IndividuCompl;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndividuComplService
{
    use EntityManagerAwareTrait;

    /** Entity managment **********************************************************************************************/

    public function create(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->getEntityManager()->persist($individuCompl);
            $this->getEntityManager()->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function update(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->getEntityManager()->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function historise(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $individuCompl->historiser();
            $this->getEntityManager()->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function restore(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $individuCompl->dehistoriser();
            $this->getEntityManager()->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    public function delete(IndividuCompl $individuCompl): IndividuCompl
    {
        try {
            $this->getEntityManager()->remove($individuCompl);
            $this->getEntityManager()->flush($individuCompl);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée", 0, $e);
        }
        return $individuCompl;
    }

    /** facade ********************************************************************************************************/

    /**
     * Retourne la liste des compléments d'individu ranger par individu
     * @return IndividuCompl[][]
     */
    public function getComplements() : array
    {
        /** @var IndividuCompl[] $result */
        $result = $this->getEntityManager()->getRepository(IndividuCompl::class)->findAll();

        $complements = [];
        foreach ($result as $item) {
            $complements[$item->getIndividu()->getId()][] = $item;
        }

        return $complements;
    }


}
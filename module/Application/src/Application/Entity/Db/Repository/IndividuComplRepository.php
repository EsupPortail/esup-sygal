<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuCompl;
use Doctrine\ORM\NonUniqueResultException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class IndividuComplRepository extends DefaultEntityRepository
{

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return IndividuCompl|null
     */
    public function findRequestedIndividuCompl(AbstractActionController $controller, string $param="individu-compl") : ?IndividuCompl
    {
        $id = $controller->params()->fromRoute($param);
        /** @var IndividuCompl|null $individuCompl */
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->find($id);
        return $individuCompl;
    }

    /**
     * @param Individu $individu
     * @return IndividuCompl|null
     */
    public function findIndividuComplByIndividu(Individu $individu) : ?IndividuCompl
    {
        $qb = $this->getEntityManager()->getRepository(IndividuCompl::class)->createQueryBuilder("individucompl")
            ->join("individucompl.individu","individu")
            ->andWhere("individucompl.individu = :individu")
            ->setParameter("individu", $individu)
            ->andWhere("individucompl.histoDestruction is NULL")
        ;

        /** @var IndividuCompl $entity */
        try {
            $entity = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs IndividuCompl ont le même individu.");
        }
        return $entity;
    }

    /**
     * @param Individu $individu
     * @return IndividuCompl[]
     */
    public function findAllIndividuComplsByIndividu(Individu $individu) : array
    {
        $qb = $this->getEntityManager()->getRepository(IndividuCompl::class)->createQueryBuilder("individucompl")
            ->join("individucompl.individu","individu")
            ->andWhere("individucompl.individu = :individu")
            ->setParameter("individu", $individu)
            ->orderBy("individuCompl.histoCreation")
        ;

        /** @var IndividuCompl[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}
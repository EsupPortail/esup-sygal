<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Service\BaseService;
use UnicaenImport\Entity\Db\Source;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Individu::class);
    }

    /**
     * @param People $people
     * @return Individu
     */
    public function createFromPeople(People $people)
    {
        $sns = (array)$people->get('sn');
        $usuel = array_pop($sns);
        $patro = array_pop($sns);
        if ($patro === null) $patro = $usuel;

        $entity = new Individu();
        $entity->setNomUsuel($usuel);
        $entity->setNomPatronymique($patro);
        $entity->setPrenom($people->get('givenName'));
        $entity->setCivilite($people->get('supannCivilite'));
        $entity->setEmail($people->get('mail'));

        /** @var Source $source */
        $entity->setSourceCode($people->get('supannEmpId'));

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $entity;
    }

    public function getIndividuByRole(Role $role) {
//        $repo = $this->entityManager->getRepository(Individu::class);
//        $qb = $repo->createQueryBuilder("in")
//            -> join (IndividuRole::class, "ir", "WITH", "ir.individu = in.id")
//            -> andWhere("ir.role = :role")
//            ->setParameter("role", $role)
//        ;
//        $query = $qb->getQuery();
//        $res = $query->execute();
//        return $res;
        $repo = $this->entityManager->getRepository(IndividuRole::class);
        $qb = $repo->createQueryBuilder("ir")
            -> join (Individu::class, "in")
            -> andWhere("ir.role = :role")
            ->setParameter("role", $role)
        ;
        $query = $qb->getQuery();
        $res = $query->execute();
        return $res;
    }
}
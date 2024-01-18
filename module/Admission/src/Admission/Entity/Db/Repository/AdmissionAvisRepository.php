<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\AdmissionAvis;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class AdmissionAvisRepository extends DefaultEntityRepository{
    /**
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findAdmissionAvisById($id): AdmissionAvis
    {
        $qb = $this->createQueryBuilder('ra')
            ->addSelect('r')
            ->join('ra.admission', 'r')
            ->where('ra = :id')->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }
}
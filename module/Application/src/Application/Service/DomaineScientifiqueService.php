<?php

namespace Application\Service;

use Application\Entity\Db\DomaineScientifique;
use Doctrine\ORM\EntityRepository;
use UnicaenApp\Exception\RuntimeException;

class DomaineScientifiqueService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getEntityManager()->getRepository(DomaineScientifique::class);
        return $repo;
    }

    /**
     * @return DomaineScientifique[]
     */
    public function getDomainesScientifiques()
    {
        $qb = $this->getRepository()->createQueryBuilder("domaine");
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int $id
     * @return DomaineScientifique
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDomaineScientifiqueById($id)
    {
        $qb = $this->getRepository()->createQueryBuilder("domaine")
            ->andWhere("domaine.id = :id")
            ->setParameter("id", $id)
        ;
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDomaineScientifiqueByLibelle($libelle)
    {
        $qb = $this->getRepository()->createQueryBuilder("domaine")
            ->andWhere("domaine.libelle = :libelle")
            ->setParameter("libelle", $libelle)
        ;
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createDomaineScientifique($libelle)
    {
        $result = $this->getDomaineScientifiqueByLibelle($libelle);
        if ($result !== null) {
            throw new RuntimeException("Il existe déjà un domaine scientifique libellé [".$libelle."]");
        }
        $domaine = new DomaineScientifique();
        $domaine->setLibelle($libelle);
        $this->getEntityManager()->persist($domaine);
        $this->getEntityManager()->flush($domaine);
        return $domaine;
    }

    /**
     * @param DomaineScientifique $domaine
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDomaineScientifique($domaine)
    {
        $this->getEntityManager()->flush($domaine);
    }

}
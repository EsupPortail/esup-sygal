<?php

namespace Admission\Service\Transmission;

use Admission\Entity\Db\Transmission;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\ORMException;
use UnicaenApp\Exception\RuntimeException;

class TransmissionService extends BaseService
{
    /**
     * @return DefaultEntityRepository
    **/
    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(Transmission::class);

        return $repo;
    }

    /**
     * @param Transmission $transmission
     * @return Transmission
     */
    public function create(Transmission $transmission) : Transmission
    {
        try {
            $this->getEntityManager()->persist($transmission);
            $this->getEntityManager()->flush($transmission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une Transmission");
        }

        return $transmission;
    }

    /**
     * @param Transmission $transmission
     * @return Transmission
     */
    public function update(Transmission $transmission)  :Transmission
    {
        try {
            $this->getEntityManager()->flush($transmission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une Transmission");
        }

        return $transmission;
    }

    /**
     * @param Transmission $transmission
     * @return Transmission
     */
    public function delete(Transmission $transmission) : Transmission
    {
        try {
            $this->getEntityManager()->remove($transmission);
            $this->getEntityManager()->flush($transmission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'une Transmission");
        }

        return $transmission;
    }
}
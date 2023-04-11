<?php

namespace Horodatage\Service\Horodatage;

use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Horodatage\Entity\Db\Horodatage;
use RuntimeException;

class HorodatageService {
    use ProvidesObjectManager;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES ***********************************************************/

    public function create(Horodatage $horodatage) : Horodatage
    {
        try {
            $this->getObjectManager()->persist($horodatage);
            $this->getObjectManager()->flush($horodatage);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $horodatage;
    }

    public function update(Horodatage $horodatage) : Horodatage
    {
        try {
            $this->getObjectManager()->persist($horodatage);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $horodatage;
    }

    public function delete(Horodatage $horodatage) : Horodatage
    {
        try {
            $this->getObjectManager()->remove($horodatage);
            $this->getObjectManager()->flush($horodatage);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $horodatage;
    }

    /** REQUETAGE ****************************************************************/

    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getObjectManager()->getRepository(Horodatage::class)->createQueryBuilder('horodatage')
            ->join('horodatage.utilisateur', 'utilisateur')->addSelect('utilisateur')
        ;
        return $qb;
    }

    public function getHorodatage(?int $id) : ?Horodatage
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('horodatage.id = :id')->setParameter('id', $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Horodatage partagent le même id [".$id."]",0,$e);
        }
        return $result;
    }

    /** FACADE **********************************************************************/

    public function createHorodatage(string $type, ?string $complement = null) : Horodatage
    {
        $timestamp = new DateTime();
        $utilisateur = $this->userContextService->getIdentityDb();

        $horodatage = new Horodatage();
        $horodatage->setDate($timestamp);
        $horodatage->setUtilisateur($utilisateur);
        $horodatage->setType($type);
        $horodatage->setComplement($complement);

        $this->create($horodatage);
        return $horodatage;
    }
}
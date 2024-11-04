<?php

namespace Application\Service\Profil;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Repository\ProfilRepository;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;

class ProfilService extends BaseService
{
    use EntityManagerAwareTrait;
    use PrivilegeServiceAwareTrait;

    public function getRepository(): ProfilRepository
    {
        return $this->entityManager->getRepository(Profil::class);
    }

    /**
     * @return Profil[]
     */
    public function getProfils()
    {
        $qb = $this->getEntityManager()->getRepository(Profil::class)->createQueryBuilder('profil')
            ->orderBy('profil.structureType, profil.libelle')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param integer $id
     * @return Profil
     */
    public function getProfil($id)
    {
        $qb = $this->getEntityManager()->getRepository(Profil::class)->createQueryBuilder('profil')
            ->andWhere('profil.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs profils partagent le même id.', $e);
        }
        return $result;
    }

    /**
     * @param Profil $profil
     * @return  Profil
     */
    public function create($profil)
    {
        $this->getEntityManager()->persist($profil);
        try {
            $this->getEntityManager()->flush($profil);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la création d'un Profil", $e);
        }
        return $profil;
    }

    /**
     * @param Profil $profil
     * @return  Profil
     */
    public function update($profil)
    {
        try {
            $this->getEntityManager()->flush($profil);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour d'un Profil", $e);
        }
        return $profil;
    }

    /**
     * @param Profil $profil
     */
    public function delete($profil)
    {
        $this->getEntityManager()->remove($profil);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la suppression d'un Profil", $e);
        }
    }

    /**
     * // TODO : /?\ DQL plutôt que cela /?\
     * Lors de l'affectation d'un profil à un rôle :
     * 1 - retrait des anciens privilèges accordés ;
     * 2 - affectation des privilèges du nouveau profil.
     * @param Profil $profil
     * @param Role $role
     */
    public function applyProfilToRole($profil, $role)
    {
        // Comment accéder à la liste des privilèges d'un rôle ??? (la relation est unidirectionnelle est c'est génant ici) ...
//        foreach ($role->getPrivileges() as $privilege) {

        $privileges = $this->getServicePrivilege()->getRepo()->findAll();
        /** @var Privilege $privilege */
        foreach ($privileges as $privilege) {
            if ($privilege->hasRole($role)) {
                $privilege->removeRole($role);
                try {
                    $this->getEntityManager()->flush($privilege);
                } catch (OptimisticLockException $e) {
                    throw new RuntimeException("Un problème est survenu lors du retrait d'un privilège", $e);
                }
            }
        }

        foreach ($profil->getPrivileges() as $privilege) {
            $privilege->addRole($role);
            try {
                $this->getEntityManager()->flush($privilege);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'ajout d'un privilège", $e);
            }
        }
    }

    /**
     * @param Profil $from
     * @param Profil $to
     */
    public function copyPrivilegeFrom($from, $to)
    {
        foreach ($to->getPrivileges() as $privilege) {
            $to->removePrivilege($privilege);
        }
        try {
            $this->getEntityManager()->flush($to);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenu lors du retrait des privilèges initiaux", $e);
        }

        foreach ($from->getPrivileges() as $privilege) {
            $to->addPrivilege($privilege);
        }
        try {
            $this->getEntityManager()->flush($to);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'ajout des privilèges", $e);
        }
    }
}
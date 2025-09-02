<?php

namespace Application\Service\Profil;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Repository\ProfilRepository;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Laminas\View\Model\JsonModel;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenPrivilege\Service\Privilege\PrivilegeServiceAwareTrait;

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
        return $qb->getQuery()->getResult();
    }

    /**
     * @param integer $id
     * @return Profil
     */
    public function getProfil($id)
    {
        $qb = $this->getEntityManager()->getRepository(Profil::class)->createQueryBuilder('profil')
            ->addSelect('r')
            ->leftJoin('profil.roles', 'r')
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
     * Ajout ou retrait d'un privilège à un profil, puis application aux rôles liés à ce profil.
     * @throws \Exception
     */
    public function inverserPrivilegeForProfil(Profil $profil, Privilege $privilege): int
    {
        // refetch avec données liées utiles
        /** @var \Application\Entity\Db\Profil $profil */
        $profil = $this->getRepository()->createQueryBuilder('profil')
            ->leftJoin('profil.privileges', 'p')->addSelect('p')
            ->leftJoin('profil.roles', 'r')->addSelect('r')
            // ne pas ajouter ->leftJoin('r.privileges', 'rp')->addSelect('rp') : trop long !
            ->where('profil = :profil')->setParameter('profil', $profil)
            ->getQuery()->getOneOrNullResult();

        if ($profil->hasPrivilege($privilege)) {
            // retrait du privilège au profil, puis aux rôles liés au profil
            $profil->removePrivilege($privilege);
            foreach ($profil->getRoles() as $role) {
                $privilege->removeRole($role);
            }
            $return = 0;
        } else {
            // ajout du privilège au profil, puis aux rôles liés au profil
            $profil->addPrivilege($privilege);
            foreach ($profil->getRoles() as $role) {
                $privilege->addRole($role);
            }
            $return = 1;
        }

        // enregistrement + déclenchement d'evt par le PrivilegeService (pour vidage du cache)
        $this->privilegeService->update();

        return $return;
    }

    /**
     * // TODO : /?\ DQL plutôt que cela /?\
     * Lors de l'affectation d'un profil à un rôle :
     * 1 - retrait des anciens privilèges accordés ;
     * 2 - affectation des privilèges du nouveau profil.
     * @param Profil $profil
     * @param Role $role
     */
    public function applyProfilToRole(Profil $profil, Role $role): void
    {
        $privileges = $this->privilegeService->getRepo()->findAll();

        /** @var Privilege $privilege */
        foreach ($privileges as $privilege) {
            if ($privilege->hasRole($role)) {
                $privilege->removeRole($role);
            }
        }

        foreach ($profil->getPrivileges() as $privilege) {
            $privilege->addRole($role);
        }

        // enregistrement + déclenchement d'evt par le PrivilegeService (pour vidage du cache)
        $this->privilegeService->update();
    }

    public function copyPrivilegeFrom(Profil $from, Profil $to): void
    {
        foreach ($to->getPrivileges() as $privilege) {
            $to->removePrivilege($privilege);
        }

        foreach ($from->getPrivileges() as $privilege) {
            $to->addPrivilege($privilege);
        }

        // enregistrement + déclenchement d'evt par le PrivilegeService (pour vidage du cache)
        $this->privilegeService->update();
    }
}
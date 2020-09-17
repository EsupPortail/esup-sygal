<?php

namespace Application\Service\Role;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Repository\RoleRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\BaseService;
use Application\Service\Profil\ProfilServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

/**
 * Class RoleService
 * @package Application\Service\Role
 */
class RoleService extends BaseService
{
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use ProfilServiceAwareTrait;

    /**
     * @return RoleRepository
     */
    public function getRepository()
    {
        /** @var RoleRepository $repo */
        $repo = $this->entityManager->getRepository(Role::class);

        return $repo;
    }

    public function getRolesByStructure(Structure $structure)
    {
        $repo = $this->entityManager->getRepository(Role::class);
        $qb = $repo->createQueryBuilder("ro")
            ->andWhere("ro.structure = :structure");
        $qb->setParameter('structure', $structure);
        return $qb->getQuery()->execute();
    }

    public function getIndividuByStructure(Structure $structure)
    {
        $individuRoles = $this->getIndividuRoleByStructure($structure);
        $individus = [];
        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $individus[] = $individuRole->getIndividu();
        }
        return $individus;
    }

    public function getIndividuRoleByStructure(Structure $structure)
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);
        $qb = $repo->createQueryBuilder("ir")
            ->leftJoin("ir.role", "r")
            ->andWhere('r.structure = :structure')
            ->setParameter("structure", $structure);
        return $qb->getQuery()->execute();
    }

    /**
     * Recherche des IndividuRole pour l'individu spécifié.
     *
     * @param Individu $individu
     * @return IndividuRole[]
     */
    public function findIndividuRolesByIndividu(Individu $individu)
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $qb = $repo->createQueryBuilder("ir")
            ->addSelect('i, r')
            ->join('ir.individu', 'i')
            ->join('ir.role', 'r')
            ->where('i = :individu')
            ->setParameter('individu', $individu);

        return $qb->getQuery()->execute();
    }

    /**
     * Recherche des IndividuRole tels que "individu.sourceCode LIKE pattern".
     *
     * @param string $individuSourceCodePattern
     * @return IndividuRole[]
     */
    public function findIndividuRolesByIndividuSourceCodePattern($individuSourceCodePattern)
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $qb = $repo->createQueryBuilder("ir")
            ->addSelect('i, r')
            ->join('ir.individu', 'i', Join::WITH, "i.sourceCode LIKE :pattern")
            ->join('ir.role', 'r')
            ->setParameter('pattern', $individuSourceCodePattern);
        return $qb->getQuery()->execute();
    }

    public function getIndividuRoleById($individuRoleId)
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);
        $individuRole = $repo->findOneBy(["id" => $individuRoleId]);
        return $individuRole;
    }

    /**
     * @param int $individuRoleId
     * @return null|object
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeIndividuRoleById($individuRoleId)
    {
        $individuRole = $this->getIndividuRoleById($individuRoleId);
        $this->entityManager->remove($individuRole);
        $this->entityManager->flush($individuRole);
        return $individuRole;
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @return IndividuRole
     */
    public function getIndividuRole($individu, $role)
    {
        $qb = $this->entityManager->getRepository(IndividuRole::class)->createQueryBuilder('ir')
            ->andWhere('ir.individu = :individu')
            ->andWhere('ir.role = :role')
            ->setParameter('individu', $individu->getId())
            ->setParameter('role', $role->getId());
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs IndividuRole ont été trouvé pour l'association [Individu(" . $individu->getId() . "),Role(" . $role->getId() . ")].", $e);
        }
        return $result;
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @return IndividuRole
     */
    public function addIndividuRole(Individu $individu, Role $role)
    {
        $result = $this->getIndividuRole($individu, $role);

        if ($result === null) {
            $ur = new IndividuRole();
            $ur->setIndividu($individu);
            $ur->setRole($role);
            $this->getEntityManager()->persist($ur);
            try {
                $this->getEntityManager()->flush($ur);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Un problème est survenu.", $e);
            }
            $result = $ur;
        }
        return $result;
    }

    /**
     * @param Individu $individu
     * @param Role $role
     */
    public function removeIndividuRole(Individu $individu, Role $role)
    {
        $result = $this->getIndividuRole($individu, $role);
        $this->getEntityManager()->remove($result);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est en BD.", $e);
        }
    }


    /**
     * @param int $structureType_id
     * @return Profil[] $roles
     */
    public function getProfilByStructureType($structureType_id)
    {
        $qb = $this->entityManager->getRepository(Profil::class)->createQueryBuilder("rm")
            //->join("TypeStructure", "ts", "WITH", "rm.structureType = ts.id")
            ->andWhere('rm.structureType = :structureType')->setParameter("structureType", $structureType_id);
        $roles = $qb->getQuery()->execute();
        return $roles;
    }

    /**
     * @param UniteRecherche|EcoleDoctorale|Etablissement $structure
     */
    public function addRoleByStructure($structure)
    {

        /** @var TypeStructure $type */
        $type = null;
        switch (true) {
            case ($structure instanceof UniteRecherche) :
                $type = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(["code" => TypeStructure::CODE_UNITE_RECHERCHE]);
                break;
            case ($structure instanceof EcoleDoctorale) :
                $type = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(["code" => TypeStructure::CODE_ECOLE_DOCTORALE]);
                break;
            case ($structure instanceof Etablissement) :
                $type = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(["code" => TypeStructure::CODE_ETABLISSEMENT]);
                break;
        }

        /** @var Profil[] $roleModeles */
        $qb = $this->entityManager->getRepository(Profil::class)->createQueryBuilder("rm")
            ->andWhere("rm.structureType = :stype")->setParameter("stype", $type);
        $roleModeles = $qb->getQuery()->execute();

        foreach ($roleModeles as $roleModele) {

            $sourceCode = null;
            $roleId = null;
            if ($structure instanceof Etablissement) {
                $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($roleModele->getRoleCode() . "_" . $structure->getSourceCode(), $structure);
                $roleId = $roleModele->getLibelle() . " " . $structure->getCode();
            } else {
                $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo($roleModele->getRoleCode() . "_" . $structure->getSourceCode());
                $roleId = $roleModele->getLibelle() . " " . $structure->getSourceCode();
            }

            $role = $this->createRole($roleModele->getRoleCode(), $roleModele->getLibelle(), $sourceCode);
            $role->setRoleId($roleId);
            $role->setTypeStructureDependant($type);
            $role->setStructure($structure->getStructure());
            try {
                $this->entityManager->flush($role);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Erreur lors de l'enregistrement du rôle '{$role->getRoleId()}'", null, $e);
            }

            /** @var Privilege $privilege */
            foreach ($roleModele->getPrivileges() as $privilege) {
                $privilege->addRole($role);
                try {
                    $this->entityManager->flush($privilege);
                } catch (OptimisticLockException $e) {
                    throw new RuntimeException(
                        "Erreur lors de l'ajout du privilège '{$privilege->getCode()}' au rôle '{$role->getRoleId()}'", null, $e);
                }
            }
        }

    }

    /**
     * @param string $code
     * @param string $libelle
     * @param string $sourceCode
     * @return Role
     */
    private function createRole($code, $libelle, $sourceCode)
    {
        $appSource = $this->sourceService->fetchApplicationSource();

        $role = new Role();
        $role->setCode($code);
        $role->setLibelle($libelle);
        $role->setSourceCode($sourceCode);
        $role->setSource($appSource);
        $role->setRoleId($libelle . " " . $appSource->getCode());
        $role->setAttributionAutomatique(false);
        $role->setTheseDependant(false);
        $role->setOrdreAffichage("zzz");

        $this->entityManager->beginTransaction();
        $this->entityManager->persist($role);
        try {
            $this->entityManager->flush($role);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new RuntimeException("Erreur lors de l'enregistrement du rôle '{$role->getRoleId()}'.", null, $e);
        }

        return $role;
    }

    public function removeRole($individuId, $roleId)
    {
        $qb = $this->getEntityManager()->getRepository(IndividuRole::class)->createQueryBuilder("ir")
            ->leftJoin("ir.individu", "i")
            ->leftJoin("ir.role", "r")
            ->andWhere("i.id = :individuId")
            ->andWhere("r.id = :roleId")
            ->setParameter("individuId", $individuId)
            ->setParameter("roleId", $roleId);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Un problème est survenu.", $e);
        }
        if ($result !== null) {
            $this->getEntityManager()->remove($result);
            try {
                $this->getEntityManager()->flush($result);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Un problème est survenu.", $e);
            }
        }
    }

    public function addRole($individuId, $roleId)
    {
        /**
         * @var Individu $individu
         * @var Role $role
         */
        $individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["id" => $individuId]);
        $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(["id" => $roleId]);

        $ir = new IndividuRole();
        $ir->setIndividu($individu);
        $ir->setRole($role);

        $this->getEntityManager()->persist($ir);
        try {
            $this->getEntityManager()->flush($ir);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenu.", $e);
        }
    }

    public function getRoles()
    {
        $result = $this->getEntityManager()->getRepository(Role::class)->findAll();
        return $result;
    }

//    /**
//     * @param Role $role
//     * @return Role
//     */
//    public function updateRole($role)
//    {
//        try {
//            $this->getEntityManager()->flush($role);
//        } catch (OptimisticLockException $e) {
//            throw new RuntimeException("Problème lors du change de l'ordre d'affichage du rôle [".$role->getId()."]");
//        }
//        return $role;
//    }


    public function getRolesEcolesDoctorales()
    {
        $typeStructureId = 2;

        $qb = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('role')
            ->andWhere('role.typeStructureDependant = :typeStructureId')
            ->setParameter('typeStructureId', $typeStructureId)
            ->join('role.structure', 'structure')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->andWhere('substitutionTo.id IS NULL');

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @return Role[]
     */
    public function getRolesSansProfil()
    {
        $qb = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('role')
            ->leftJoin('role.profils', 'profil')
            ->andWhere('profil.id IS NULL')
            ->orderBy('role.roleId', 'ASC');

        $roles = $qb->getQuery()->getResult();
        return $roles;
    }

    /**
     * @param Role $role
     */
    public function removeProfils($role)
    {
        foreach ($role->getProfils() as $profil) {
            $role->removeProfil($profil);
            try {
                $this->getEntityManager()->flush($role);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Un problème est survenu lors de la déassociation entre le role et le profil.", $e);
            }
        }
    }

    /**
     * @param ArrayCollection $roles
     * @param Privilege $privilege
     * @param boolean $etat
     */
    public function applyChangement($roles, $privilege, $etat)
    {
        /** @var Role $role */
        foreach ($roles as $role) {
            if ($etat) {
                $privilege->addRole($role);
            } else {
                $privilege->removeRole($role);
            }
            try {
                $this->getEntityManager()->flush($privilege);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'application du changement du profil aux rôles associés.", $e);
            }
        }
    }

    public function getRolesAsGroupOptions()
    {
        /** @var Profil[] $profils */
        $profils = [];
        $profils[] = $this->getProfilService()->getProfil(11);
        $profils[] = $this->getProfilService()->getProfil(101);
        $profils[] = $this->getProfilService()->getProfil(121);
        //$profils[] = $this->getProfilService()->getProfil(161);

        $groupOptions = [];
        foreach ($profils as $profil) {
            $result = [];
            $result["label"] = $profil->getLibelle();
            $result["options"] = [];
            /** @var Role $role */
            foreach ($profil->getRoles() as $role) {
                $this_option = [
                    'value' => $role->getId(),
//                    'attributes' => [
//                        'data-content' => "<span class='badge ".$competence->getType()->getLibelle()."'>".$competence->getType()->getLibelle()."</span> &nbsp;". $competence->getLibelle(),
//                    ],
                    'label' => $role->getLibelle() . " (" . $role->getStructure()->getLibelle(). ")" ,
                ];
                $result["options"][] = $this_option;

            }
            $groupOptions[] = $result;
        }
        return $groupOptions;
    }

//SELECT * FROM ROLE R
//JOIN STRUCTURE S on R.STRUCTURE_ID = S.ID
//LEFT JOIN STRUCTURE_SUBSTIT SS on S.ID = SS.FROM_STRUCTURE_ID
//WHERE TYPE_STRUCTURE_DEPENDANT_ID = 2
//  and SS.TO_STRUCTURE_ID IS NULL

}
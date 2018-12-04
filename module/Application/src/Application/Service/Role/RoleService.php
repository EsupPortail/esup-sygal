<?php

namespace Application\Service\Role;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Repository\RoleRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\RoleModele;
use Application\Entity\Db\RolePrivilegeModele;
use Application\Entity\Db\Source;
use Application\Entity\Db\Structure;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Utilisateur;
use Application\Filter\EtablissementPrefixFilter;
use Application\Filter\EtablissementPrefixFilterAwareTrait;
use Application\Service\BaseService;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class RoleService
 * @package Application\Service\Role
 */
class RoleService extends BaseService
{
    use EtablissementPrefixFilterAwareTrait;

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
        $repo = $this->entityManager->getRepository( Role::class);
        $qb = $repo->createQueryBuilder("ro")
            ->andWhere("ro.structure = :structure")
        ;
        $qb->setParameter('structure', $structure);
        return $qb->getQuery()->execute();
    }

    public function getIndividuByStructure(Structure $structure) {
       $individuRoles = $this->getIndividuRoleByStructure($structure);
        $individus = [];
        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $individus[] = $individuRole->getIndividu();
        }
        return $individus;
    }

    public function getIndividuRoleByStructure(Structure $structure) {
        $repo = $this->entityManager->getRepository( IndividuRole::class);
        $qb = $repo->createQueryBuilder("ir")
            ->leftJoin("ir.role","r")
            ->andWhere('r.structure = :structure')
            ->setParameter("structure", $structure);
        return $qb->getQuery()->execute();
    }

    /**
     * @param int $individuSourceCode
     * @return IndividuRole[]
     */
    public function getIndividuRolesByIndividuSourceCode($individuSourceCode)
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $filter = new EtablissementPrefixFilter();
        $pattern = $filter->addSearchPatternPrefix($individuSourceCode);

        $qb = $repo->createQueryBuilder("ro")
            ->addSelect('i, r')
            ->join('ro.individu', 'i', Join::WITH, "i.sourceCode LIKE :pattern")
            ->join('ro.role', 'r')
            ->setParameter('pattern', $pattern);
        return $qb->getQuery()->execute();
    }

    public function getIndividuRoleById($individuRoleId) {
        $repo = $this->entityManager->getRepository(IndividuRole::class);
        $individuRole = $repo->findOneBy(["id" => $individuRoleId]);
        return $individuRole;
    }

    /**
     * @param int $individuRoleId
     * @return null|object
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeIndividuRoleById($individuRoleId) {
        $individuRole = $this->getIndividuRoleById($individuRoleId);
        $this->entityManager->remove($individuRole);
        $this->entityManager->flush($individuRole);
        return $individuRole;
    }

    public function addIndividuRole(Individu $individu, Role $role) {
        $ur = new IndividuRole();
        $ur->setIndividu($individu);
        $ur->setRole($role);
        $this->getEntityManager()->persist($ur);
        $this->getEntityManager()->flush($ur);
        return $ur;
    }


    /**
     * @param int $structureType_id
     * @return RoleModele[] $roles
     */
    public function getRoleModeleByStructureType($structureType_id) {
        $qb = $this->entityManager->getRepository(RoleModele::class)->createQueryBuilder("rm")
            //->join("TypeStructure", "ts", "WITH", "rm.structureType = ts.id")
            ->andWhere('rm.structureType = :structureType')->setParameter("structureType", $structureType_id)
        ;
        $roles = $qb->getQuery()->execute();
        return $roles;
    }

    public function getRoleModeleByStructures() {
        $roleModele = [];
        $structures = $this->entityManager->getRepository(TypeStructure::class)->findAll();

        /** @var TypeStructure $structure */
        foreach ($structures as $structure) {
            $roles = $this->getRoleModeleByStructureType($structure->getId());
            $roleModele[$structure->getLibelle()] = $roles;
        }

        return $roleModele;
    }

    /**
     * @param UniteRecherche|EcoleDoctorale|Etablissement $structure
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addRoleByStructure($structure) {

        /** @var TypeStructure $type */
        $type = null;
        switch(true) {
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

        /** @var RoleModele[] $roleModeles */
        $qb = $this->entityManager->getRepository(RoleModele::class)->createQueryBuilder("rm")
            ->andWhere("rm.structureType = :stype")->setParameter("stype", $type);
        $roleModeles = $qb->getQuery()->execute();

        foreach ($roleModeles as $roleModele) {

            $sourceCode = null;
            $roleId = null;
            if ($structure instanceof Etablissement) {
                $sourceCode = $this->getEtablissementPrefixFilter()->addPrefixEtablissementTo($roleModele->getRoleCode(), $structure);
                $roleId = $roleModele->getLibelle()." ". $structure->getCode();
            } else {
                $sourceCode = $this->getEtablissementPrefixFilter()->addPrefixEtablissementTo($roleModele->getRoleCode());
                $roleId = $roleModele->getLibelle()." ". $structure->getSourceCode();
            }

            //creation du role
            $role = $this->createRole();
            $role->setCode($roleModele->getRoleCode());
            $role->setLibelle($roleModele->getLibelle());
            $role->setSourceCode($sourceCode);
            $role->setRoleId($roleId);
            $role->setTypeStructureDependant($type);
            $role->setStructure($structure->getStructure());
            $this->entityManager->flush($role);

            //affectation du modèle de privilège
            /** @var RolePrivilegeModele[] $privileges */
            $qbs = $this->entityManager->getRepository(RolePrivilegeModele::class)->createQueryBuilder("rpm")
                ->andWhere("rpm.roleCode = :roleCode")->setParameter("roleCode", $roleModele->getRoleCode());
            $privileges = $qbs->getQuery()->execute();
            foreach ($privileges as $privilegeModele) {
                $privilege = $privilegeModele->getPrivilege();
                $privilege->addRole($role);
                $this->entityManager->flush($privilege);
            }
        }

    }


    public function createRole($libelle = "Aucun")
    {
        /** @var Source $sourceSygal */
        $sourceSygal = $this->entityManager->getRepository(Source::class)->findOneBy(["code" => Source::CODE_SYGAL]);
        /** @var Utilisateur $userSygal */
        $userSygal = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(["username" => "sygal-app"]);

        $role = new Role();
        $role->setCode("SyGAL");
        $role->setLibelle($libelle);
        $role->setSourceCode("SyGAL::" . $libelle);
        $role->setSource($sourceSygal);
        $role->setRoleId($libelle . "SyGAL");
        $role->setAttributionAutomatique(false);
        $role->setTheseDependant(false);
        $role->setHistoCreateur($userSygal);
        $role->setHistoCreation(new \DateTime());
        $role->setHistoModificateur($userSygal);
        $role->setHistoModification(new \DateTime());
        $role->setOrdreAffichage("zzz");

        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush($role);
        return $role;
    }

    public function removeRole($individuId, $roleId)
    {
        $qb = $this->getEntityManager()->getRepository(IndividuRole::class)->createQueryBuilder("ir")
            ->leftJoin("ir.individu","i")
            ->leftJoin("ir.role","r")
            ->andWhere("i.id = :individuId")
            ->andWhere("r.id = :roleId")
            ->setParameter("individuId", $individuId)
            ->setParameter("roleId", $roleId);
        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result !== null) {
            $this->getEntityManager()->remove($result);
            $this->getEntityManager()->flush($result);
        }
    }

    public function addRole($individuId, $roleId)
    {
        $individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["id"=>$individuId]);
        $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(["id"=>$roleId]);

        $ir = new IndividuRole();
        $ir->setIndividu($individu);
        $ir->setRole($role);

        $this->getEntityManager()->persist($ir);
        $this->getEntityManager()->flush($ir);
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

//SELECT * FROM ROLE R
//JOIN STRUCTURE S on R.STRUCTURE_ID = S.ID
//LEFT JOIN STRUCTURE_SUBSTIT SS on S.ID = SS.FROM_STRUCTURE_ID
//WHERE TYPE_STRUCTURE_DEPENDANT_ID = 2
//  and SS.TO_STRUCTURE_ID IS NULL

}
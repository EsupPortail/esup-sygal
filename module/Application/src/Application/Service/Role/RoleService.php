<?php

namespace Application\Service\Role;

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Profil;
use Application\Entity\Db\Repository\RoleRepository;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Profil\ProfilServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\RuntimeException;

class RoleService extends BaseService
{
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use ProfilServiceAwareTrait;

    /**
     * @return RoleRepository
     */
    public function getRepository(): RoleRepository
    {
        /** @var RoleRepository $repo */
        $repo = $this->entityManager->getRepository(Role::class);

        return $repo;
    }

    /**
     * Recherche de rôles par structure.
     *
     * @param \Structure\Entity\Db\Structure $structure
     * @return Role[]
     */
    public function findRolesForStructure(Structure $structure): array
    {
        /** @var \Application\Entity\Db\Repository\DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(Role::class);
        $qb = $repo->createQueryBuilder("r")
            ->join('r.structure', 's')->addSelect('s')
            ->andWhereStructureIs($structure)
            ->orderBy('r.ordreAffichage');

        return $qb->getQuery()->execute();
    }

    /**
     * Recherche des individus ayant un rôle lié à la structure spécifiée.
     *
     * @param \Structure\Entity\Db\Structure $structure
     * @return Individu[]
     */
    public function findIndividuForStructure(Structure $structure): array
    {
        $individuRoles = $this->findIndividuRoleByStructure($structure);
        $individus = [];
        foreach ($individuRoles as $individuRole) {
            $individus[] = $individuRole->getIndividu();
        }

        return $individus;
    }

    /**
     * Recherche d'IndividuRole par structure du Role, éventuellement code du Role et périmètre établissement.
     *
     * @return \Individu\Entity\Db\IndividuRole[]
     */
    public function findIndividuRoleByStructure(Structure $structure,
                                                ?string $role = null,
                                                ?Etablissement $perimetreEtablissement = null): array
    {
        /** @var \Application\Entity\Db\Repository\DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $qb = $repo->createQueryBuilder("ir")
            ->join("ir.individu", "i")->addSelect('i')
            ->join("ir.role", "r")->addSelect('r')
            ->leftJoin('r.structure', 's')->addSelect('s')
            ->leftJoin('ir.individuRoleEtablissement', 'irp')->addSelect('irp')
            ->leftJoin('irp.etablissement', 'irpe')->addSelect('irpe')
            ->leftJoin('irpe.structure', 'irpes')->addSelect('irpes')
            ->andWhereStructureIs($structure)
            ->andWhereNotHistorise('i')
            ->andWhereNotHistorise('r')
            ->orderBy('r.ordreAffichage, s.libelle, i.nomUsuel, i.prenom1');

        if ($role !== null) {
            $qb->andWhere('r.code = :role')->setParameter('role', $role);
        }

        // Si un établissement est spécifié, filtrage selon ce périmètre.
        if ($perimetreEtablissement === null) {
            $qb->andWhere('irpe is null or irpe = :etablissement')->setParameter('etablissement', $perimetreEtablissement);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Recherche des IndividuRole pour l'individu spécifié.
     *
     * @param Individu $individu
     * @return IndividuRole[]
     */
    public function findIndividuRolesByIndividu(Individu $individu): array
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $qb = $repo->createQueryBuilder("ir")
            ->addSelect('i, r')
            ->join('ir.individu', 'i')
            ->join('ir.role', 'r')
            ->leftJoin('r.structure', 's')->addSelect('s')
            ->where('i = :individu')
            ->andWhere('pasHistorise(r) = 1')
            ->andWhere('s.id is null or s.histoDestruction is null')
            ->setParameter('individu', $individu);

        return $qb->getQuery()->execute();
    }

    /**
     * Recherche des IndividuRole tels que "individu.sourceCode LIKE pattern".
     *
     * @param string $individuSourceCodePattern
     * @return IndividuRole[]
     */
    public function findIndividuRolesByIndividuSourceCodePattern(string $individuSourceCodePattern): array
    {
        $repo = $this->entityManager->getRepository(IndividuRole::class);

        $qb = $repo->createQueryBuilder("ir")
            ->addSelect('i, r')
            ->join('ir.individu', 'i', Join::WITH, "i.sourceCode LIKE :pattern")
            ->join('ir.role', 'r')
            ->leftJoin('r.structure', 's')->addSelect('s')
            ->andWhere('pasHistorise(r) = 1')
            ->andWhere('s.id is null or s.histoDestruction is null')
            ->setParameter('pattern', $individuSourceCodePattern);
        return $qb->getQuery()->execute();
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @return IndividuRole|null
     */
    public function findOneIndividuRole(Individu $individu, Role $role): ?IndividuRole
    {
        $qb = $this->entityManager->getRepository(IndividuRole::class)->createQueryBuilder('ir')
            ->andWhere('ir.individu = :individu')
            ->andWhere('ir.role = :role')
            ->setParameter('individu', $individu->getId())
            ->setParameter('role', $role->getId());
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                "Plusieurs IndividuRole ont été trouvés pour [Individu(" . $individu->getId() . "),Role(" . $role->getId() . ")].",
                null,
                $e
            );
        }
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @return IndividuRole
     */
    public function addIndividuRole(Individu $individu, Role $role): IndividuRole
    {
        $result = $this->findOneIndividuRole($individu, $role);

        if ($result === null) {
            $ur = new IndividuRole();
            $ur->setIndividu($individu);
            $ur->setRole($role);
            try {
                $this->getEntityManager()->persist($ur);
                $this->getEntityManager()->flush($ur);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'enregistrement de l'IndividuRole.", null, $e);
            }
            $result = $ur;
        }

        return $result;
    }

    public function filterIndividuRolePotentielDirecteurThese(array $individuRoles): array
    {
        return array_filter($individuRoles, function(IndividuRole $ir) {
            return $ir->getRole()->getCode() === Role::CODE_ADMISSION_DIRECTEUR_THESE;
        });
    }

    public function filterIndividuRolePotentielCoDirecteurThese(array $individuRoles): array
    {
        return array_filter($individuRoles, function(IndividuRole $ir) {
            return $ir->getRole()->getCode() === Role::CODE_ADMISSION_CODIRECTEUR_THESE;
        });
    }

    public function filterIndividuRoleCandidat(array $individuRoles): array
    {
        return array_filter($individuRoles, function(IndividuRole $ir) {
            return $ir->getRole()->getCode() === Role::CODE_ADMISSION_CANDIDAT;
        });
    }

    /**
     * @param UniteRecherche|EcoleDoctorale|Etablissement $structure
     */
    public function addRoleByStructure($structure): void
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

        /** @var Profil[] $profils */
        $qb = $this->entityManager->getRepository(Profil::class)->createQueryBuilder("rm")
            ->andWhere("rm.structureType = :stype")->setParameter("stype", $type);
        $profils = $qb->getQuery()->execute();

        foreach ($profils as $profil) {
            if ($structure instanceof Etablissement) {
                $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($profil->getRoleCode() . "_" . $structure->getSourceCode(), $structure);
                $roleId = trim($profil->getLibelle() . " " . $structure->getStructure()->getSourceCode());
            } else {
                $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo($profil->getRoleCode() . "_" . $structure->getSourceCode());
                $roleId = trim($profil->getLibelle() . " " . $structure->getStructure()->getCode());
            }

            $role = $this->createRole($profil->getRoleCode(), $profil->getLibelle(), $sourceCode);
            $role->setRoleId($roleId);
            $role->setTypeStructureDependant($type);
            $role->setStructure($structure->getStructure());
            $role->addProfil($profil);
            try {
                $this->entityManager->flush($role);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur lors de l'enregistrement du rôle '{$role->getRoleId()}'", null, $e);
            }

            /** @var Privilege $privilege */
            foreach ($profil->getPrivileges() as $privilege) {
                $privilege->addRole($role);
                try {
                    $this->entityManager->flush($privilege);
                } catch (ORMException $e) {
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
    private function createRole(string $code, string $libelle, string $sourceCode): Role
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
        try {
            $this->entityManager->persist($role);
            $this->entityManager->flush($role);
            $this->entityManager->commit();
        } catch (ORMException $e) {
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
            throw new RuntimeException("Un problème est survenu.", null, $e);
        }
        if ($result !== null) {
            try {
                $this->getEntityManager()->remove($result);
                $this->getEntityManager()->flush($result);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de la suppression de l'IndividuRole.", null, $e);
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

        try {
            $this->getEntityManager()->persist($ir);
            $this->getEntityManager()->flush($ir);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu lors de la création de l'IndividuRole.", null, $e);
        }
    }

    /**
     * @param string|null $typeStructureCode null = NON structure-dépendant
     * @return Role[]
     */
    public function findRolesByTypeStructureDependant(?string $typeStructureCode): array
    {
        $qb = $this->getRepository()->createQueryBuilder('r')
            ->andWhere('r.theseDependant = :theseDependant')
            ->setParameter('theseDependant', false);

        if ($typeStructureCode !== null) {
            $qb
                ->addSelect('ts, s')
                ->join('r.typeStructureDependant', 'ts', Join::WITH, 'ts.code = :codeTypeStructure')
                ->setParameter('codeTypeStructure', $typeStructureCode)
                ->join('r.structure', 's')
                ->andWhereNotHistorise('s');

            switch ($typeStructureCode) {
                case TypeStructure::CODE_ETABLISSEMENT:
                    $qb
                        ->addSelect('e')
                        ->join('s.etablissement', 'e')
                        ->andWhereNotHistorise('e');
                    break;
                case TypeStructure::CODE_ECOLE_DOCTORALE:
                    $qb
                        ->addSelect('ed')
                        ->join('s.ecoleDoctorale', 'ed')
                        ->andWhereNotHistorise('ed');
                    break;
                case TypeStructure::CODE_UNITE_RECHERCHE:
                    $qb
                        ->addSelect('ur')
                        ->join('s.uniteRecherche', 'ur')
                        ->andWhereNotHistorise('ur');
                    break;
            }
        } else {
            $qb->andWhere('r.typeStructureDependant is null');
        }

        $qb
            ->andWhere('r.roleId <> :roleCode')
            ->setParameter('roleCode', 'user'); // rôle 'Authentifié' écarté

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Role[]
     */
    public function findRolesSansProfil(): array
    {
        $qb = $this->getEntityManager()->getRepository(Role::class)->createQueryBuilder('role')
            ->leftJoin('role.profils', 'profil')
            ->andWhere('profil.id IS NULL')
            ->orderBy('role.roleId', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Role $role
     */
    public function removeProfils(Role $role)
    {
        foreach ($role->getProfils() as $profil) {
            $role->removeProfil($profil);
            try {
                $this->getEntityManager()->flush($role);
            } catch (ORMException $e) {
                throw new RuntimeException("Un problème est survenu lors de la déassociation entre le role et le profil.", null, $e);
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
            } catch (ORMException $e) {
                throw new RuntimeException(
                    "Un problème est survenu lors de l'application du changement du profil aux rôles associés.",
                    null,
                    $e
                );
            }
        }
    }
}
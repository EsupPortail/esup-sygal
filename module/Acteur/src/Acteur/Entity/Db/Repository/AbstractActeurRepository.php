<?php

namespace Acteur\Entity\Db\Repository;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use RuntimeException;
use Soutenance\Entity\Membre;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;

abstract class AbstractActeurRepository extends DefaultEntityRepository
{
    public function findActeurByIndividuAndEntity(Individu $individu, These|HDR $entity): ActeurThese|ActeurHDR|null
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise() // indispensable
            ->andWhere('a.individu = :individu')
            ->setParameter('individu', $individu)
            ->orderBy('a.id', 'DESC');

        if ($entity instanceof These) {
            $qb->andWhere('a.these = :entity');
        } else {
            $qb->andWhere('a.hdr = :entity');
        }
        $qb->setParameter('entity', $entity);

        $acteurs = $qb->getQuery()->getResult();

        return $acteurs[0] ?? null;
    }

    /**
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function findActeurByEntity(These|HDR $entity): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('role')->join('a.role', 'role');

        if ($entity instanceof These) {
            $qb->andWhere('a.these = :entity');
        } else {
            $qb->andWhere('a.hdr = :entity');
        }
        $qb->setParameter('entity', $entity);
        
        return $qb->getQuery()->getResult();
    }

    /**
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function findActeursByEntityAndRole(These|HDR $entity, Role|string $role): array
    {
        $code = $role;
        if ($role instanceof Role) {
            $code = $role->getCode();
        }

        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('role.code = :code')
            ->setParameter('code', $code)
            ->andWhere('acteur.histoDestruction is null')
            ->orderBy('acteur.ordre');

        if ($entity instanceof These) {
            $qb->andWhere('acteur.these = :entity');
        } else {
            $qb->andWhere('acteur.hdr = :entity');
        }
        $qb->setParameter('entity', $entity);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function findEncadrementEntity(These|HDR $entity): array
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('acteur.histoDestruction is null');

        if ($entity instanceof These) {
            $qb
                ->andWhere('acteur.these = :entity')
                ->andWhere('role.code = :directeur OR role.code = :codirecteur')
                ->setParameter('directeur', Role::CODE_DIRECTEUR_THESE)
                ->setParameter('codirecteur', Role::CODE_CODIRECTEUR_THESE);
        } else {
            $qb
                ->andWhere('acteur.hdr = :entity')
                ->andWhere('role.code = :garant')
                ->setParameter('garant', Role::CODE_HDR_GARANT);
        }
        $qb->setParameter('entity', $entity);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function findActeursForIndividu(Individu $individu): array
    {
        $qb = $this->createQueryBuilder('acteur')
//            ->addSelect('these')->join('acteur.these', 'these')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('acteur.histoDestruction is null')
            ->andWhere('acteur.individu = :individu')
            ->setParameter('individu', $individu)
            /*->orderBy('these.id', 'ASC')*/;

        if (get_class($this) === ActeurTheseRepository::class) {
            $qb->join('acteur.these', 'ent');
        } else {
            $qb->join('acteur.hdr', 'ent');
        }
        $qb
            ->addSelect('ent')
            ->orderBy('ent.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche d'acteurs selon leur rôle.
     *
     * @param Role|string $role Rôle, ou code du rôle (ex: {@see Role::CODE_DIRECTEUR_THESE})
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param EcoleDoctorale|string|array|null $ecoleDoctorale {@see EcoleDoctorale}, code structure de l'ED, ou ['s.sigle' => 'ED 591 NBISE'] par ex.
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function findActeursByRole(
        Role|string $role,
        Etablissement|null $etablissement = null,
        EcoleDoctorale|string|array|null $ecoleDoctorale = null): array
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $qb = $this->createQueryBuilder('a')
            ->addSelect('i, r')
            ->join('a.individu', 'i')
            ->join('a.role', 'r', Join::WITH, 'r.code = :role')->setParameter('role', $role)
            ->andWhere('a.histoDestruction is null')
            ->addOrderBy('i.nomUsuel, i.prenom1');

        if (get_class($this) === ActeurTheseRepository::class) {
            $qb->join('a.these', 'ent', Join::WITH, 'ent.etatThese = :etat');
        } else {
            $qb->join('a.hdr', 'ent', Join::WITH, 'ent.etatHdr = :etat');
        }
        $qb
            ->addSelect('ent')
            ->setParameter('etat', These::ETAT_EN_COURS)
            ->join('ent.ecoleDoctorale', 'ed')->addSelect('ed')
            ->join('ed.structure', 's')->addSelect('s');

        if ($ecoleDoctorale !== null) {
            if ($ecoleDoctorale instanceof EcoleDoctorale) {
                $qb
                    ->andWhere('s = :structure')
                    ->setParameter('structure', $ecoleDoctorale->getStructure(/*false*/));
            } elseif (is_array($ecoleDoctorale)) {
                $leftPart = key($ecoleDoctorale);
                $rightPart = current($ecoleDoctorale);
                $qb
                    ->andWhere(sprintf($leftPart, 's') . ' = :value')
                    ->setParameter('value', $rightPart);
            } else {
                $qb
                    ->andWhere('s.code = :code')
                    ->setParameter('code', $ecoleDoctorale);
            }
        }

        if ($etablissement !== null) {
            $qb
                ->join('ent.etablissement', 'e')->addSelect('e')
                ->join('e.structure', 'etab_structure')//->addSelect('etab_structure')
                ->andWhereStructureIs($etablissement->getStructure(/*false*/), 'etab_structure');
        }

        return $qb->getQuery()->getResult();
    }

//    /** Recupération des présidents de jurys **************************************************************************/
//
//    /**
//     * @return ActeurThese[]|ActeurHDR[]
//     */
//    public function findActeursPresidentDuJuryForThesesAvecCorrection(): array
//    {
//        $qb = $this->createQueryBuilder('a')
//            ->addSelect('i')->join('a.individu', 'i')
////            ->addSelect('t')->join('a.these', 't')
//            ->addSelect('m')->leftJoin('a.membre', 'm')
//            ->addSelect('r')->join('a.role', 'r')
//            ->addSelect('s')->leftJoin('r.structure', 's')
//            ->andWhere('r.code = :president')
//            ->setParameter('president', Role::CODE_PRESIDENT_JURY)
//            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
////            ->andWhere("t.correctionAutorisee is not null or t.correctionAutoriseeForcee is not null")
//            ->andWhere('a.histoDestruction is null')
//            ->andWhere('i.histoDestruction is null')
////            ->andWhere('t.histoDestruction is null')
//            /*->orderBy('t.dateSoutenance', 'DESC')*/;
//
//        if (get_class($this) === ActeurTheseRepository::class) {
//            $qb
//                ->addSelect('ent')->join('a.these', 'ent')
//                ->andWhere("ent.correctionAutorisee is not null or ent.correctionAutoriseeForcee is not null")
//                ->andWhere('ent.histoDestruction is null')
//                ->orderBy('ent.dateSoutenance', 'DESC');
//        } else {
//            $qb
//                ->addSelect('ent')->join('a.hdr', 'ent')
//                //->andWhere("ent.correctionAutorisee is not null or ent.correctionAutoriseeForcee is not null")
//                ->andWhere('ent.histoDestruction is null')
//                /*->orderBy('ent.dateSoutenance', 'DESC')*/;
//        }
//
//        return $qb->getQuery()->getResult();
//    }

    public function findActeurForSoutenanceMembre(Membre $soutenanceMembre): ActeurThese|ActeurHDR|null
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')->join('a.individu', 'i')
//            ->addSelect('t')->join('a.these', 't')
            ->addSelect('m')->join('a.membre', 'm')
            ->addSelect('r')->join('a.role', 'r')
            ->addSelect('s')->leftJoin('r.structure', 's')
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->andWhere('a.histoDestruction is null')
            ->andWhere('i.histoDestruction is null')
            ->andWhere('m = :membre')->setParameter('membre', $soutenanceMembre);

        if (get_class($this) === ActeurTheseRepository::class) {
            $qb->addSelect('ent')->join('a.these', 'ent');
        } else {
            $qb->addSelect('ent')->join('a.hdr', 'ent');
        }
        
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : plusieurs membres pour un même acteur", null, $e);
        }
    }

    /**
     * @param Membre[] $rapporteurs
     * @return ActeurThese[]
     */
    public function findActeursForSoutenanceMembres(array $rapporteurs): array
    {
        $acteurs = [];
        foreach ($rapporteurs as $rapporteur) {
            $acteurs[$rapporteur->getId()] = $this->findActeurForSoutenanceMembre($rapporteur);
        }

        return $acteurs;
    }

    public function findActeurByIndividuAndEntityAndRole(Individu $individu, These|HDR $entity, Role|string $role): ActeurThese|ActeurHDR|null
    {
        $code = $role;
        if ($role instanceof Role) {
            $code = $role->getCode();
        }

        $qb = $this->createQueryBuilder('a')
            ->addSelect('role')->join('a.role', 'role')
            ->andWhereNotHistorise() // indispensable
            ->andWhere('a.individu = :individu')
            ->andWhere('role.code = :code')
            ->setParameter('code', $code)
            ->setParameter('individu', $individu)
            ->orderBy('a.id', 'DESC');

        if ($entity instanceof These) {
            $qb->andWhere('a.these = :entity');
        } else {
            $qb->andWhere('a.hdr = :entity');
        }
        $qb->setParameter('entity', $entity);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : plusieurs acteurs pour les caractéristiques données", null, $e);
        }
    }
}
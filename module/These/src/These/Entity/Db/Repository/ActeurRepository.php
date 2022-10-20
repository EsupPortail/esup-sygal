<?php

namespace These\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;

class ActeurRepository extends DefaultEntityRepository
{
    /**
     * @param Individu $individu
     * @param These $these
     * @return Individu|null
     */
    public function findActeurByIndividuAndThese(Individu $individu, These $these): ?Individu
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise()
            ->andWhere('a.individu = :individu')
            ->andWhere('a.these = :these')
            ->setParameter('individu', $individu)
            ->setParameter('these', $these)
            ->orderBy('a.id', 'DESC');

        $acteurs = $qb->getQuery()->getResult();

        return (empty($acteurs))?null:$acteurs[0];
    }

    /**
     * @param These $these
     * @return Acteur[]
     */
    public function findActeurByThese(These $these): array
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * @param These $these
     * @param Role|string $role
     * @return Acteur[]
     */
    public function findActeursByTheseAndRole(These $these, $role): array
    {
        $code = $role;
        if ($role instanceof Role) $code = $role->getCode();
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('role.code = :code')
            ->setParameter('code', $code)
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these)
            ->andWhere('acteur.histoDestruction is null');

        $qb
            ->addSelect('structureSubstituante')->leftJoin("structure.structureSubstituante", "structureSubstituante")
            ->addSelect('etablissementSubstituant')->leftJoin("structureSubstituante.etablissement", "etablissementSubstituant")
            ->addSelect('ecoleDoctoraleSubstituant')->leftJoin("structureSubstituante.ecoleDoctorale", "ecoleDoctoraleSubstituant")
            ->addSelect('uniteRechercheSubstituant')->leftJoin("structureSubstituante.uniteRecherche", "uniteRechercheSubstituant");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param These $these
     * @return Acteur[]
     */
    public function findEncadrementThese(These $these): array
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('role.code = :directeur OR role.code = :codirecteur')
            ->setParameter('directeur', Role::CODE_DIRECTEUR_THESE)
            ->setParameter('codirecteur', Role::CODE_CODIRECTEUR_THESE)
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these)
            ->andWhere('acteur.histoDestruction is null');

        $qb
            ->addSelect('structureSubstituante')->leftJoin("structure.structureSubstituante", "structureSubstituante")
            ->addSelect('etablissementSubstituant')->leftJoin("structureSubstituante.etablissement", "etablissementSubstituant")
            ->addSelect('ecoleDoctoraleSubstituant')->leftJoin("structureSubstituante.ecoleDoctorale", "ecoleDoctoraleSubstituant")
            ->addSelect('uniteRechercheSubstituant')->leftJoin("structureSubstituante.uniteRecherche", "uniteRechercheSubstituant");

        return $qb->getQuery()->getResult();
    }


    /**
     * @param Individu $individu
     * @return Acteur[]
     */
    public function findActeursByIndividu(Individu $individu): array
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->addSelect('role')->join('acteur.role', 'role')
            ->addSelect('structure')->leftJoin('role.structure', 'structure')
            ->andWhere('acteur.histoDestruction is null')
            ->andWhere('acteur.individu = :individu')
            ->setParameter('individu', $individu)
            ->orderBy('these.id', 'ASC');

        $qb
            ->addSelect('ss')->leftJoin("structure.structureSubstituante", "ss")
            ->addSelect('etablissementSubstituant')->leftJoin("ss.etablissement", "etablissementSubstituant")
            ->addSelect('ecoleDoctoraleSubstituant')->leftJoin("ss.ecoleDoctorale", "ecoleDoctoraleSubstituant")
            ->addSelect('uniteRechercheSubstituant')->leftJoin("ss.uniteRecherche", "uniteRechercheSubstituant");

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche d'acteurs selon leur rôle.
     *
     * NB : les seules ED prises en compte sont celles non substituées.
     *
     * NB : ici pas de jointure vers l'éventuelle établissement substitué, charge à l'appelant de passer un
     * l'établissement adéquat (substitué ou non).
     *
     * @param Role|string $role Rôle, ou code du rôle (ex: {@see Role::CODE_DIRECTEUR_THESE})
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param EcoleDoctorale|string|array|null $ecoleDoctorale {@see EcoleDoctorale}, code structure de l'ED, ou ['s.sigle' => 'ED 591 NBISE'] par ex.
     * @return Acteur[]
     */
    public function findActeursByRole(
        $role,
        Etablissement $etablissement = null,
        $ecoleDoctorale = null): array
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $qb = $this->createQueryBuilder('a')
            ->addSelect('i, r, t, ed, s')
            ->join('a.individu', 'i')
            ->join('a.role', 'r', Join::WITH, 'r.code = :role')->setParameter('role', $role)
            ->join('a.these', 't', Join::WITH, 't.etatThese = :etat')->setParameter('etat', These::ETAT_EN_COURS)
            ->join('t.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('a.histoDestruction is null')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1');

        // l'ED ne doit pas être substituée
        $qb->addSelect('structureSubstituante')
            ->leftJoin("s.structureSubstituante", "structureSubstituante")
            ->andWhere("structureSubstituante is null");

        if ($ecoleDoctorale !== null) {
            if ($ecoleDoctorale instanceof EcoleDoctorale) {
                $qb->andWhere('ed = :ed');
            } elseif (is_array($ecoleDoctorale)) {
                $qb->andWhere(key($ecoleDoctorale) . ' = :ed');
                $ecoleDoctorale = current($ecoleDoctorale);
            } else {
                $qb->andWhere('s.code = :ed');
            }
            $qb->setParameter('ed', $ecoleDoctorale);
        }

        if ($etablissement !== null) {
            $qb->join('t.etablissement', 'e', Join::WITH, 'e = :etab')->setParameter('etab', $etablissement);
        }

        return $qb->getQuery()->getResult();
    }

    /** Recupération des présidents de jurys **************************************************************************/

    /**
     * @return Acteur[]
     */
    public function fetchPresidentDuJuryTheseAvecCorrection(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')->join('a.individu', 'i')
            ->addSelect('t')->join('a.these', 't')
            ->addSelect('m')->join('a.membre', 'm')
            ->addSelect('r')->join('a.role', 'r')
            ->addSelect('s')->leftJoin('r.structure', 's')
            ->andWhere('r.code = :president')
            ->setParameter('president', Role::CODE_PRESIDENT_JURY)
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->andWhere("t.correctionAutorisee is not null or t.correctionAutoriseeForcee is not null")
            ->andWhere('a.histoDestruction is null')
            ->andWhere('i.histoDestruction is null')
            ->andWhere('t.histoDestruction is null')
            ->orderBy('t.dateSoutenance', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
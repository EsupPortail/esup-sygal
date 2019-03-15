<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\UserWrapper;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 */
class EtablissementRepository extends DefaultEntityRepository
{
    /**
     * Retourne l'établissement "inconnu".
     *
     * Cet établissement est utilisé pour rattacher un individu utilisateur à un établissement lorsque
     * son EPPN contient un domaine inconnu.
     *
     * @return Etablissement|null
     */
    public function fetchEtablissementInconnu()
    {
        $etab = $this->findOneBySourceCode($sourceCode = Etablissement::SOURCE_CODE_ETABLISSEMENT_INCONNU);
        if ($etab === null) {
            throw new RuntimeException("Anomalie: l'établissement 'inconnu' doit exister dans la BDD (SOURCE_CODE='$sourceCode')");
        }

        return $etab;
    }

    /**
     * Recherche un établissement par son code de structure.
     *
     * @param string $code Ex: 'ENSICAEN'
     * @return Etablissement|null
     */
    public function findOneByCodeStructure($code)
    {
        $qb = $this->getEntityManager()->getRepository(Etablissement::class)->createQueryBuilder("e")
            ->join("e.structure","structure")
            ->andWhere("structure.code = :code")
            ->setParameter("code", $code);

        /** @var Etablissement $entity */
        try {
            $entity = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs établissements ont le même code structure.");
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return null|Etablissement
     */
    public function find($id) {
        /** @var Etablissement $etablissement */
        $etablissement = $this->findOneBy(["id" => $id]);
        return $etablissement;
    }

    /**
     * @return Etablissement[]
     */
    public function findAll()
    {
        /** @var Etablissement[] $etablissments */
        $qb = $this->createQueryBuilder("et")
            ->join("et.structure", "str")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        $etablissements = $qb->getQuery()->getResult();

        return $etablissements;
    }

    /**
     * @param string source
     * @param boolean $include (si 'true' alors seulement la source sinon tous sauf la source)
     * @return Etablissement[]
     */
    public function findAllBySource($source, $include = true)
    {
        $qb = $this->createQueryBuilder("e")
            ->join("e.source", "s")
            ->join("e.structure", "str")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        if ($include) {
            $qb->andWhere("s.code = :source");
        } else {
            $qb->andWhere("s.code != :source");
        }
        $qb->setParameter("source", $source);

        /** @var Etablissement[] $etablissments */
        $etablissments = $qb->getQuery()->execute();

        return $etablissments;
    }

    /**
     * Recherche un établissement par son domaine DNS.
     *
     * @param string $domaine Ex: "unicaen.fr"
     * @return Etablissement|null
     */
    public function findOneByDomaine($domaine)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.domaine = :domaine')
            ->setParameter('domaine', $domaine);

        try {
            /** @var Etablissement $etab */
            $etab = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs établissements trouvés avec ce domaine: " . $domaine);
        }

        return $etab;
    }

    /**
     * Tente de trouver l'établissement auquel appartient un utilisateur.
     *
     * @param UserWrapper $userWrapper Utilisateur
     * @return Etablissement|null
     */
    public function findOneForUserWrapper(UserWrapper $userWrapper)
    {
        $domaine = $userWrapper->getDomainFromEppn() ?: $userWrapper->getDomainFromEmail();
        if (! $domaine) {
            throw new RuntimeException("Cas imprévu: aucun domaine exploitable.");
        }

        $etablissement = $this->findOneByDomaine($domaine);

        return $etablissement;
    }

    /**
     * Recherche un établissement par son source_code.
     *
     * @param string $sourceCode Ex: 'UCN::CRHEA'
     * @return Etablissement|null
     */
    public function findOneBySourceCode($sourceCode)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.sourceCode = :sourceCode')
            ->setParameter('sourceCode', $sourceCode);

        try {
            /** @var Etablissement $etab */
            $etab = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs établissements trouvés avec ce source code: " . $sourceCode);
        }

        return $etab;
    }

    public function findByStructureId($structureId)
    {
        $qb = $this->createQueryBuilder("e")
            ->addSelect("s")
            ->join("e.structure", "s")
            ->andWhere("s.id = :structureId")
            ->setParameter("structureId", $structureId);
        try {
            $etablissement = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs établissements avec le même id.", 0, $e);
        }

        return $etablissement;
    }

    public function findOneByLibelle($libelle)
    {
        $qb = $this->createQueryBuilder("e")
            ->addSelect("s")
            ->join("e.structure", "s")
            ->andWhere("s.libelle = :structureId")
            ->setParameter("structureId", $libelle);
        try {
            $etablissement = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs établissements avec le même id.", 0, $e);
        }

        return $etablissement;
    }

    /**
     * @return Etablissement[]
     */
    public function findAllEtablissementsMembres()
    {
        $qb = $this->createQueryBuilder("e")
            ->addSelect("s")
            ->join("e.structure", "s")
            ->andWhere("e.estMembre = 1")
            ->orderBy('s.libelle')
        ;

        return  $qb->getQuery()->getResult();
    }
}
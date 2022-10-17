<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\Etablissement;
use Application\Entity\UserWrapper;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 */
class EtablissementRepository extends DefaultEntityRepository
{
    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);
        $qb
            ->addSelect('structure')
            ->join("$alias.structure", 'structure')
            ->addSelect('structureSubstituante')
            ->leftJoin("structure.structureSubstituante", 'structureSubstituante')
            ->addSelect('etablissementSubstituant')
            ->leftJoin("structureSubstituante.etablissement", 'etablissementSubstituant');

        return $qb;
    }

    /**
     * Retourne l'établissement "inconnu".
     *
     * Cet établissement est utilisé pour rattacher un individu utilisateur à un établissement lorsque
     * son EPPN contient un domaine inconnu.
     *
     * @return Etablissement
     */
    public function fetchEtablissementInconnu(): Etablissement
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
    public function findOneByCodeStructure(string $code): ?Etablissement
    {
        $qb = $this->createQueryBuilder("e")
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
     * @param bool $ouverte
     * @return Etablissement[]
     */
    public function findAll(bool $ouverte = false): array
    {
        /** @var Etablissement[] $etablissments */
        $qb = $this->createQueryBuilder("et")
            ->leftJoin("structure.structuresSubstituees", "sub")
            ->leftJoin("structure.typeStructure", "typ")
            ->addSelect("sub, typ")
            ->orderBy("structure.libelle");

        if ($ouverte) {
            $qb = $qb->andWhere('structure.estFermee = false')
                ->andWhere('structureSubstituante IS NULL')
                ->orderBy('structure.sigle')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Etablissement[]
     */
    public function findSubstituables(): array
    {
        $qb = $this->createQueryBuilder("ed");
        $qb
            ->addSelect("typ")
            ->leftJoin("structure.typeStructure", "typ")
            ->addSelect("structuresSubstituees")
            ->leftJoin("structure.structuresSubstituees", "structuresSubstituees")
            ->andWhere('structure.estFermee = false')
            ->andWhere('structureSubstituante IS NULL')
            ->andWhere('structuresSubstituees IS NULL')
            ->orderBy("structure.libelle");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string source
     * @param boolean $include (si 'true' alors seulement la source sinon tous sauf la source)
     * @return Etablissement[]
     */
    public function findAllBySource($source, bool $include = true): array
    {
        $qb = $this->createQueryBuilder("e")
            ->join("e.source", "s")
            ->leftJoin("structure.structuresSubstituees", "sub")
            ->leftJoin("structure.typeStructure", "typ")
            ->addSelect("sub, typ")
            ->orderBy("structure.libelle");

        if ($include) {
            $qb->andWhere("s.code = :source");
        } else {
            $qb->andWhere("s.code != :source");
        }
        $qb->setParameter("source", $source);

        return $qb->getQuery()->execute();
    }

    /**
     * Recherche un établissement par son domaine DNS.
     *
     * @param string $domaine Ex: "unicaen.fr"
     * @return Etablissement|null
     */
    public function findOneByDomaine(string $domaine): ?Etablissement
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
    public function findOneForUserWrapper(UserWrapper $userWrapper): ?Etablissement
    {
        $domaine = $userWrapper->getDomainFromEppn() ?: $userWrapper->getDomainFromEmail();
        if (! $domaine) {
            throw new RuntimeException("Cas imprévu: aucun domaine exploitable.");
        }

        return $this->findOneByDomaine($domaine);
    }

    /**
     * Recherche un établissement par son source_code.
     *
     * @param string $sourceCode Ex: 'UCN::CRHEA'
     * @return Etablissement|null
     */
    public function findOneBySourceCode(string $sourceCode): ?Etablissement
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

    public function findByStructureId($structureId): ?Etablissement
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere("structure.id = :structureId")
            ->setParameter("structureId", $structureId);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs établissements avec le même id.", 0, $e);
        }
    }

    /**
     * @return Etablissement[]
     */
    public function findAllEtablissementsMembres(): array
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere("e.estMembre = true")
            ->orderBy('structure.libelle');

        return  $qb->getQuery()->getResult();
    }

    /**
     * @param bool $cacheable
     * @return Etablissement[]
     */
    public function findAllEtablissementsInscriptions(bool $cacheable = false): array
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere("e.estInscription = true")
            ->orderBy('structure.libelle');

        // structure non substituée
        $qb->andWhere("structureSubstituante is null");

        $qb->setCacheable($cacheable);

        return  $qb->getQuery()->getResult();
    }

    /**
     * @param string|null $term
     * @return Etablissement[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere('lower(structure.libelle) like :term or lower(structure.sigle) like :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->andWhere('e.histoDestruction is null')
            ->andWhere('structure.estFermee = :false')
            ->setParameter('false', false)
            ->andWhere('structureSubstituante IS NULL');

        return $qb->getQuery()->getResult();
    }
}
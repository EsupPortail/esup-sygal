<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\UserWrapper;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 */
class EtablissementRepository extends DefaultEntityRepository
{
    use StructureConcreteRepositoryTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = $this->_createQueryBuilder($alias);

        return $qb;
    }

    /**
     * @return Etablissement[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder("e");
        $qb->orderBy("structure.libelle");

        return $this->_findAll($qb);
    }

    public function findByStructureId($structureId, bool $nonHistorise = true): ?Etablissement
    {
        $qb = $this->createQueryBuilder("e");
        if ($nonHistorise) {
            $qb->andWhereNotHistorise('e');
        }

        return $this->_findByStructureId($qb, $structureId);
    }

    /**
     * @param string|null $term
     * @return array[]
     */
    public function findByText(?string $term) : array
    {
        if (strlen($term) < 2) return [];

        $qb = $this->findByTextQb($term);

        return $qb->getQuery()->getArrayResult();
    }

    public function findByTextQb(?string $term): DefaultQueryBuilder
    {
        $qb = $this->createQueryBuilder("e");

        return $this->_findByTextQb($qb, $term);
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
     * @param string source
     * @param boolean $include (si 'true' alors seulement la source sinon tous sauf la source)
     * @return Etablissement[]
     */
    public function findAllBySource($source, bool $include = true): array
    {
        $qb = $this->createQueryBuilder("e")
            ->join("e.source", "s")
            ->leftJoin("structure.typeStructure", "typ")
            ->addSelect("typ")
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
            ->orderBy('structure.libelle')
            ->andWhereNotHistorise('e');

        $qb->setCacheable($cacheable);

        return  $qb->getQuery()->getResult();
    }
}
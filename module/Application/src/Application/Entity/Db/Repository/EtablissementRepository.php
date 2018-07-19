<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 */
class EtablissementRepository extends DefaultEntityRepository
{
    /**
     * Cette fonction retourne le libellé associé au code d'un établissement
     * @param $code
     * @return string|null
     * @throws NonUniqueResultException
     */
    public function libelle($code)
    {
        $qb = $this->getEntityManager()->getRepository(Etablissement::class)->createQueryBuilder("etablissement")
            ->leftJoin("etablissement.structure","structure")
            ->andWhere("structure.code = :code")
            ->setParameter("code", $code)
            ;
        /** @var Etablissement $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        return $entity ? $entity->getLibelle() : null;
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
    public function findAll() {
        /** @var Etablissement[] $etablissments */
        $qb = $this->createQueryBuilder("et")
            ->leftJoin("et.structure", "str", "WITH", "et.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle")
        ;
        $etablissements = $qb->getQuery()->getResult();
        return $etablissements;
    }

    /**
     * @param string source
     * @param boolean $include (si 'true' alors seulement la source sinon tous sauf la source)
     * @return Etablissement[]
     */
    public function findAllBySource($source , $include=true) {
        $qb = $this->createQueryBuilder("e")
            ->join("e.source", "s");

        if ($include) {
            $qb = $qb->andWhere("s.code = :source");
        } else {
            $qb = $qb->andWhere("s.code != :source");
        }
        $qb = $qb->setParameter("source", $source);

        /** @var Etablissement[] $etablissments */
        $etablissments = $qb->getQuery()->execute();
        return $etablissments;
    }

    /**
     * Recherche un établissement par son domaine DNS.
     *
     * @param string $domaine Ex: "unicaen.fr"
     * @return Etablissement
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
     * Recherche un établissement par son code.
     *
     * @param string $code Ex: 'UCN'
     * @return Etablissement|null
     */
    public function findOneByCode($code)
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.structure', 's')
            ->where('s.code = :code')
            ->setParameter('code', $code);

        try {
            /** @var Etablissement $etab */
            $etab = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs établissements trouvés avec ce code: " . $code);
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
}
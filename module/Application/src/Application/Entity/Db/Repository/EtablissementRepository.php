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
}
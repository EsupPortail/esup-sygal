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
     */
    public function libelle($code)
    {
        /** @var Etablissement $entity */
        $entity = $this->findOneBy(['code' => $code]);

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
}
<?php

namespace Application\Entity\Db\Repository;

use Structure\Entity\Db\Etablissement;

class VersionDiplomeRepository extends DefaultEntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('vdi')
            ->join('vdi.etablissement', 'e')->addSelect('e')
            ->join('e.structure', 'es')->addSelect('es')
            ->addOrderBy('es.sigle', 'ASC')
            ->addOrderBy('vdi.libelleLong', 'ASC');

        return $qb->indexBy('vdi', 'vdi.id')->getQuery()->getResult();
    }

    public function findForEtablissement(Etablissement|string $etablissement): array
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getSourceCode();
        }

        $qb = $this->createQueryBuilder('vdi')
            ->join('vdi.etablissement', 'e')->addSelect('e')
            ->where("e.sourceCode = :etablissement")
            ->setParameter("etablissement", $etablissement)
            ->orderBy('vdi.libelleLong', 'ASC');

        return $qb->indexBy('vdi', 'vdi.id')->getQuery()->getResult();
    }
}
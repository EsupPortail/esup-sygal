<?php

namespace Application\Entity\Db\Repository;

use Structure\Entity\Db\Etablissement;

class VersionDiplomeRepository extends DefaultEntityRepository
{
    public function findForEtablissement(Etablissement|string $etablissement): array
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getSourceCode();
        }

        $qb = $this->createQueryBuilder('vdi')
            ->join('vdi.etablissement', 'e')
            ->where("e.sourceCode = :etablissement")
            ->setParameter("etablissement", $etablissement)
            ->orderBy('vdi.libelleLong', 'ASC');

        return $qb->indexBy('vdi', 'vdi.code')->getQuery()->getResult();
    }
}
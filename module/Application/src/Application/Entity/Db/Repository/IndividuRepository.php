<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;

class IndividuRepository extends DefaultEntityRepository
{
    /**
     * @param string $empId
     * @param Etablissement $etablissement
     * @return Individu
     */
    public function findOneByEmpIdAndEtab($empId, Etablissement $etablissement)
    {
        /** @var Individu $i */
        $i = $this->findOneBy(['sourceCode' => $etablissement->getCode() . '::' . $empId]);

        return $i;
    }
}
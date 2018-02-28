<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;

class IndividuRepository extends DefaultEntityRepository
{
    /**
     * @param string $empId
     * @param string $etablissement
     * @return Individu
     */
    public function findOneByEmpId($empId, $etablissement)
    {
        /** @var Individu $i */
        $i = $this->findOneBy(['sourceCode' => $etablissement . '::' . $empId]);

        return $i;
    }
}
<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Variable;

/**
 *
 */
class VariableRepository extends DefaultEntityRepository
{
    /**
     * @param $sourceCode
     * @return string|null
     */
    public function valeur($sourceCode)
    {
        /** @var Variable $entity */
        $entity = $this->findOneBy(['sourceCode' => $sourceCode]);

        return $entity ? $entity->getValeur() : null;
    }
}
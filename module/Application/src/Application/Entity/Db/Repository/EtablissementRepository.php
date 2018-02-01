<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
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
}
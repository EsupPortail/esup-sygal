<?php

namespace Acteur\Filter\ActeurThese;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Filter\AbstractActeursFormatter;
use Structure\Entity\Db\Etablissement;

class ActeursTheseFormatter extends AbstractActeursFormatter
{
    /**
     * @param \Acteur\Entity\Db\ActeurThese $acteur
     */
    protected function doFormatArrayActeur(AbstractActeur $acteur): array
    {
        $result = parent::doFormatArrayActeur($acteur);

        if ($this->displayEtablissement === true) {
            $result["etablissementForce"] = ($etab = $acteur->getEtablissementForce()) ? $etab->getStructure()->getLibelle() : null;
        }

        return $result;
    }

    /**
     * @param \Acteur\Entity\Db\ActeurThese $acteur
     */
    protected function getEtablissementActeur(AbstractActeur $acteur): ?Etablissement
    {
        return $acteur->getEtablissementForce() ?: $acteur->getEtablissement();
    }
}
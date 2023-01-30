<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use UnicaenDbImport\Entity\Db\AbstractSource;

/**
 * Source
 */
class Source extends AbstractSource
{
    protected ?Etablissement $etablissement = null;

    /**
     * Retourne l'éventuel établissement lié *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.etablissement' puis 'etablissement.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'établissement substituant s'il y en a un ; sinon l'établissement d'origine.
     * @see Etablissement::getEtablissementSubstituant()
     * @return Etablissement|null
     */
    public function getEtablissement(bool $returnSubstitIfExists = true): ?Etablissement
    {
        if ($returnSubstitIfExists && $this->etablissement && ($sustitut = $this->etablissement->getEtablissementSubstituant())) {
            return $sustitut;
        }

        return $this->etablissement;
    }
}
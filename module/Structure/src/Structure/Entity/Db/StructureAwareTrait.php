<?php

namespace Structure\Entity\Db;

/**
 * Pour les classes d'entités ayant une relation to-one avec Structure.
 */
trait StructureAwareTrait
{
    /**
     * Structure (abstraite) liée.
     *
     * @var \Structure\Entity\Db\Structure|null
     */
    protected ?Structure $structure = null;

    /**
     * @param Structure $structure
     * @return self
     */
    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Retourne l'éventuelle structure liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante'.
     *
     * @param bool $returnSubstitIfExists À true, retourne la structure substituante s'il y en a une ; sinon la structure d'origine.
     * @see Structure::getStructureSubstituante()
     * @return Structure|null
     */
    public function getStructure(bool $returnSubstitIfExists = true): ?Structure
    {
        if ($returnSubstitIfExists && $this->structure && ($sustitut = $this->structure->getStructureSubstituante())) {
            return $sustitut;
        }

        return $this->structure;
    }
}
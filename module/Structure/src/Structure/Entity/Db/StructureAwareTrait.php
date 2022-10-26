<?php

namespace Structure\Entity\Db;

/**
 * Pour les classes d'entités ayant une relation to-one avec {@see \Structure\Entity\Db\Structure}.
 */
trait StructureAwareTrait
{
    /**
     * Structure *abstraite* liée.
     *
     * @var \Structure\Entity\Db\Structure|null
     */
    protected ?Structure $structure = null;

    /**
     * Change la structure *abstraite* liée.
     *
     * @param Structure $structure
     *
     * @return self
     */
    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Retourne l'éventuelle structure *abstraite* qui substitue la structure *abstraite* liée.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante'.
     *
     * @return \Structure\Entity\Db\Structure|null
     */
    protected function getStructureSubstituanteEventuelle(): ?Structure
    {
        if ($this->structure !== null) {
            return $this->structure->getStructureSubstituante();
        }

        return null;
    }

    /**
     * Retourne l'éventuelle structure liée *ou la structure substituante de cette dernière (le cas échéant)*.
     *
     * **Attention** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante'.
     *
     * @param bool $returnSubstitIfExists Si `true` : retourne la structure *abstraite* substituante, le cas échéant.
     * Si `false` : retourne la structure d'origine.
     * Si vous ne savez pas trop quelle valeur mettre, laissez la valeur par défaut (`true`, pour retourner si possible
     * la structure substituante) qui convient pour l'immense majorité des cas. Pour rappel : l'application interdit
     * de substituer une structure qui substitue déjà des structures, d'où le conseil de laisser la valeur par défaut.
     *
     * @return \Structure\Entity\Db\Structure|null
     */
    public function getStructure(bool $returnSubstitIfExists = true): ?Structure
    {
        if ($returnSubstitIfExists && ($sustituante = $this->getStructureSubstituanteEventuelle())) {
            return $sustituante;
        }

        return $this->structure;
    }
}
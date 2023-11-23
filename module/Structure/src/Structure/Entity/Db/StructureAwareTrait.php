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
     * @return StructureConcreteInterface
     */
    public function setStructure(Structure $structure): StructureConcreteInterface
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Retourne l'éventuelle structure liée.
     *
     * @return \Structure\Entity\Db\Structure|null
     */
    public function getStructure(): ?Structure
    {
        return $this->structure;
    }
}
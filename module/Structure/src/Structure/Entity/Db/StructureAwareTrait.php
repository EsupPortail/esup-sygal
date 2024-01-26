<?php

namespace Structure\Entity\Db;

/**
 * Pour les classes d'entités ayant une relation to-one avec {@see Structure}.
 */
trait StructureAwareTrait
{
    /**
     * Structure *abstraite* liée.
     *
     * @var Structure|null
     */
    protected ?Structure $structure = null;

    /**
     * Change la structure *abstraite* liée.
     *
     * @param Structure $structure
     * @return void
     */
    public function setStructure(Structure $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * Retourne l'éventuelle structure liée.
     *
     * @return Structure|null
     */
    public function getStructure(): ?Structure
    {
        return $this->structure;
    }
}
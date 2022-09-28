<?php

namespace Structure\Entity\Db;

interface StructureConcreteInterface extends StructureInterface
{
    /**
     * @param Structure $structure
     * @return self
     */
    public function setStructure(Structure $structure): self;

    /**
     * @return Structure
     */
    public function getStructure(): ?Structure;
}
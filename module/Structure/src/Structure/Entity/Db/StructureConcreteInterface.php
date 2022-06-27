<?php

namespace Structure\Entity\Db;

interface StructureConcreteInterface extends StructureInterface
{
    /**
     * @return Structure
     */
    public function getStructure();
}
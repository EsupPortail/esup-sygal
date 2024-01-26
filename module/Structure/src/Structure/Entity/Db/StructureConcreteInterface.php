<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\Source;

interface StructureConcreteInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getSourceCode();

    /**
     * @return Source
     */
    public function getSource();

    /**
     * @param Structure $structure
     * @return void
     */
    public function setStructure(Structure $structure): void;

    /**
     * Retourne l'éventuelle structure liée *ou son substitut le cas échéant*.
     *
     * @return Structure|null
     */
    public function getStructure(): ?Structure;
}
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
    public function getSourceCode();

    /**
     * @return Source
     */
    public function getSource();

    /**
     * @param Structure $structure
     * @return self
     */
    public function setStructure(Structure $structure): self;

    /**
     * Retourne l'éventuelle structure liée *ou son substitut le cas échéant*.
     *
     * @param bool $returnSubstitIfExists À true, retourne la structure substituante s'il y en a une ; sinon la structure d'origine.
     * @return Structure|null
     */
    public function getStructure(bool $returnSubstitIfExists = true): ?Structure;
}
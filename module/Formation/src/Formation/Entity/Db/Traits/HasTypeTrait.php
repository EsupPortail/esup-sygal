<?php

namespace Formation\Entity\Db\Traits;

use Structure\Entity\Db\Structure;
use Formation\Entity\Db\Interfaces\HasTypeInterface;

trait HasTypeTrait {

    private ?string $type = null;
    private ?Structure $typeStructure = null;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return HasTypeInterface
     */
    public function setType(?string $type): HasTypeInterface
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Retourne l'éventuelle structure liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.typeStructure' puis 'typeStructure.structureSubstituante'.
     *
     * @param bool $returnSubstitIfExists À true, retourne la structure substituante s'il y en a une. Sinon la structure d'origine.
     * @return Structure|null
     */
    public function getTypeStructure(bool $returnSubstitIfExists = true): ?Structure
    {
        if ($returnSubstitIfExists && $this->typeStructure && ($sustitut = $this->typeStructure->getStructureSubstituante())) {
            return $sustitut;
        }

        return $this->typeStructure;
    }

    /**
     * @param Structure|null $typeStructure
     * @return HasTypeInterface
     */
    public function setTypeStructure(?Structure $typeStructure): HasTypeInterface
    {
        $this->typeStructure = $typeStructure;
        return $this;
    }




}
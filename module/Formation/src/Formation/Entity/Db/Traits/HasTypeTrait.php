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
     * @return Structure|null
     */
    public function getTypeStructure(): ?Structure
    {
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
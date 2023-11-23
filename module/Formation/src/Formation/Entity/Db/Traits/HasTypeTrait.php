<?php

namespace Formation\Entity\Db\Traits;

use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Structure\Entity\Db\Structure;

trait HasTypeTrait
{
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
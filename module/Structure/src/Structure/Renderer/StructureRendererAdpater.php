<?php

namespace Structure\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Structure\Entity\Db\StructureConcreteInterface;

class StructureRendererAdpater extends AbtractRendererAdapter
{
    protected StructureConcreteInterface $structureConcrete;

    public function __construct(StructureConcreteInterface $structure)
    {
        $this->structureConcrete = $structure;
    }

    public function getSigle(): string
    {
        return $this->structureConcrete->getStructure()->getSigle();
    }
    public function __toString(): string
    {
        return $this->structureConcrete->__toString();
    }
}
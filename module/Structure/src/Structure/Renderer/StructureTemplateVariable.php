<?php

namespace Structure\Renderer;

use Structure\Entity\Db\StructureConcreteInterface;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class StructureTemplateVariable extends AbstractTemplateVariable
{
    protected StructureConcreteInterface $structureConcrete;

    public function setStructureConcrete(StructureConcreteInterface $structureConcrete): void
    {
        $this->structureConcrete = $structureConcrete;
    }

    /** @noinspection PhpUnused */
    public function getSigle(): string
    {
        return $this->structureConcrete->getStructure()->getSigle() ?? "";
    }

    /** @noinspection PhpUnused */
    public function __toString(): string
    {
        return $this->structureConcrete->__toString();
    }
}
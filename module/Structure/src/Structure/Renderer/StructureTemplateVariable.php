<?php

namespace Structure\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Structure\Entity\Db\StructureConcreteInterface;

/**
 * @property StructureConcreteInterface $target
 */
class StructureTemplateVariable extends AbstractTemplateVariable
{
    public function setStructureConcrete(StructureConcreteInterface $structureConcrete): void
    {
        $this->setTarget($structureConcrete);
    }

    /** @noinspection PhpUnused */
    public function getSigle(): string
    {
        return $this->target->getStructure()->getSigle() ?? "";
    }

    /** @noinspection PhpUnused */
    public function getLibelle(): string
    {
        return $this->target->getStructure()->getLibelle();
    }

    /** @noinspection PhpUnused */
    public function __toString(): string
    {
        return $this->target->__toString();
    }
}
<?php

namespace Structure\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Webmozart\Assert\Assert;

/**
 * StructureSubstit
 */
class StructureSubstit implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var integer
     */
    private int $id;

    /**
     * @var Structure
     */
    private Structure $fromStructure;

    /**
     * @var Structure
     */
    private Structure $toStructure;

    /**
     * StructureSubstit factory.
     *
     * @param \Structure\Entity\Db\StructureConcreteInterface[]\Structure\Entity\Db\StructureInterface[] $structuresSources
     * @param Structure   $structureCible
     * @return self[]
     */
    public static function fromStructures(array $structuresSources, Structure $structureCible): array
    {
        return array_map(function($structureSource) use ($structureCible) {
            if ($structureSource instanceof Etablissement ||
                $structureSource instanceof EcoleDoctorale ||
                $structureSource instanceof UniteRecherche) {
                $structureSource = $structureSource->getStructure();
            }
            Assert::isInstanceOf($structureSource, Structure::class);

            $ss = new StructureSubstit();
            $ss->setFromStructure($structureSource);
            $ss->setToStructure($structureCible);
            return $ss;
        }, $structuresSources);
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param \Structure\Entity\Db\Structure|null $fromStructure
     * @return StructureSubstit
     */
    public function setFromStructure(Structure $fromStructure = null): self
    {
        $this->fromStructure = $fromStructure;

        return $this;
    }

    /**
     * @return Structure
     */
    public function getFromStructure(): Structure
    {
        return $this->fromStructure;
    }

    /**
     * @param Structure $toStructure
     * @return StructureSubstit
     */
    public function setToStructure(Structure $toStructure): self
    {
        $this->toStructure = $toStructure;

        return $this;
    }

    /**
     * @return Structure
     */
    public function getToStructure(): Structure
    {
        return $this->toStructure;
    }
}


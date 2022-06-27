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
    private $id;

    /**
     * @var Structure
     */
    private $fromStructure;

    /**
     * @var Structure
     */
    private $toStructure;

    /**
     * StructureSubstit factory.
     *
     * @param Structure[] $structuresSources
     * @param Structure   $structureCible
     * @return self[]
     */
    public static function fromStructures(array $structuresSources, Structure $structureCible)
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromStructure
     *
     * @param Structure $fromStructure
     *
     * @return StructureSubstit
     */
    public function setFromStructure(Structure $fromStructure = null)
    {
        $this->fromStructure = $fromStructure;

        return $this;
    }

    /**
     * Get fromStructure
     *
     * @return Structure
     */
    public function getFromStructure()
    {
        return $this->fromStructure;
    }

    /**
     * Set toStructure
     *
     * @param Structure $toStructure
     *
     * @return StructureSubstit
     */
    public function setToStructure(Structure $toStructure = null)
    {
        $this->toStructure = $toStructure;

        return $this;
    }

    /**
     * Get toStructure
     *
     * @return Structure
     */
    public function getToStructure()
    {
        return $this->toStructure;
    }
}


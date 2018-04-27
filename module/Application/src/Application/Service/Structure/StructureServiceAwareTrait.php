<?php

namespace Application\Service\Structure;

/**
 * @author Unicaen
 */
trait StructureServiceAwareTrait
{
    /**
     * @var StructureService
     */
    protected $structureService;

    /**
     * @param StructureService $structureService
     * @return self
     */
    public function setStructureService(StructureService $structureService)
    {
        $this->structureService = $structureService;

        return $this;
    }

    /**
     * @return StructureService
     */
    public function getStructureService()
    {
        return $this->structureService;
    }
}
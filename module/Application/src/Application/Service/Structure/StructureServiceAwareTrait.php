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
    protected $serviceStructureStructure;

    /**
     * @param StructureService $structureService
     * @return self
     */
    public function setStructureService(StructureService $structureService)
    {
        $this->serviceStructureStructure = $structureService;

        return $this;
    }

    /**
     * @return StructureService
     */
    public function getServiceStructureStructure()
    {
        return $this->serviceStructureStructure;
    }
}
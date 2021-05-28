<?php

namespace Application\Service\StructureDocument;

trait StructureDocumentServiceAwareTrait {

    /** @var StructureDocumentService */
    private $structureDocumentService;

    /**
     * @return StructureDocumentService
     */
    public function getStructureDocumentService(): StructureDocumentService
    {
        return $this->structureDocumentService;
    }

    /**
     * @param StructureDocumentService $structureDocumentService
     * @return StructureDocumentService
     */
    public function setStructureDocumentService(StructureDocumentService $structureDocumentService): StructureDocumentService
    {
        $this->structureDocumentService = $structureDocumentService;
        return $this->structureDocumentService;
    }

}
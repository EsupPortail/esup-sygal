<?php

namespace Structure\Service\StructureDocument;

trait StructureDocumentServiceAwareTrait {

    protected StructureDocumentService $structureDocumentService;

    /**
     * @return StructureDocumentService
     */
    public function getStructureDocumentService(): StructureDocumentService
    {
        return $this->structureDocumentService;
    }

    /**
     * @param StructureDocumentService $structureDocumentService
     */
    public function setStructureDocumentService(StructureDocumentService $structureDocumentService): void
    {
        $this->structureDocumentService = $structureDocumentService;
    }

}
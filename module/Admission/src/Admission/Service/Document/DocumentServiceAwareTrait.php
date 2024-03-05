<?php

namespace Admission\Service\Document;

trait DocumentServiceAwareTrait {

    protected DocumentService $documentService;

    /**
     * @return DocumentService
     */
    public function getDocumentService(): DocumentService
    {
        return $this->documentService;
    }

    /**
     * @param DocumentService $documentService
     */
    public function setDocumentService(DocumentService $documentService): void
    {
        $this->documentService = $documentService;
    }

}
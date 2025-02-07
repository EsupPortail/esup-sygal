<?php

namespace Application\Export\Xlsx\Writer;

trait XlsxWriterManagerAwareTrait
{
    protected XlsxWriterManager $xlsxWriterManager;

    public function setXlsxWriterManager(XlsxWriterManager $xlsxWriterManager): void
    {
        $this->xlsxWriterManager = $xlsxWriterManager;
    }
}
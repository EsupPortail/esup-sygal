<?php

namespace Application\Export\Xlsx\Writer\PhpOffice;

use Application\Export\Xlsx\Writer\XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PhpOfficeXlsxWriter extends XlsxWriter
{
    private string $writerType = IOFactory::WRITER_XLSX;

    /**
     * **ATTENTION** : détruit les éventuels tableaux croisés dynamiques existants (diagrammes ?).
     */
    public function writeToFile(string $outpuFilePath): void
    {
        $reader = IOFactory::createReader($this->writerType);
        $spreadsheet = $reader->load($this->inputFilePath);
        foreach ($this->data as $tabName => $data) {
            $sheet1 = $spreadsheet->getSheetByName($tabName);
            $sheet1->fromArray($data);
        }
        $writer = IOFactory::createWriter($spreadsheet, $this->writerType);

        $writer->save($outpuFilePath);
    }
}
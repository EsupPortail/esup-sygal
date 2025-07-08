<?php

namespace Application\Export\Xlsx\Writer\ExcelDataTables;

use Application\Export\Xlsx\Writer\XlsxWriter;
use Svrnm\ExcelDataTables\ExcelDataTable;

/**
 * NB : ne détruit pas les tableaux croisés dynamiques existants
 */
class ExcelDataTablesXlsxWriter extends XlsxWriter
{
    public function writeToFile(string $outpuFilePath): void
    {
        $inputFilePath = $this->inputFilePath;

        foreach ($this->data as $tabName => $data) {
            $dataTable = new ExcelDataTable();
            $dataTable
                ->setSheetName($tabName)
                ->showHeaders()
                ->addRows($data)
                ->attachToFile($inputFilePath, $outpuFilePath, true);
            unset($dataTable);

            $inputFilePath = $outpuFilePath;
        }
    }
}
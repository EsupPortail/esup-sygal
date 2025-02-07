<?php

namespace Application\Export\Xlsx\Manager;

trait XlsxExportManagerAwareTrait
{
    protected XlsxExportManager $formationXlsxExportManager;

    public function setFormationXlsxExportManager(XlsxExportManager $formationXlsxExportManager): void
    {
        $this->formationXlsxExportManager = $formationXlsxExportManager;
    }
}
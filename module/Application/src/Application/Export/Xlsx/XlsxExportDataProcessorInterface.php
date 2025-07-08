<?php

namespace Application\Export\Xlsx;

interface XlsxExportDataProcessorInterface
{
    /**
     * Traite/transforme les données spécifiées.
     *
     * @param array $data Données à traiter
     * @return array Données transformées
     */
    public function processData(array $data): array;
}
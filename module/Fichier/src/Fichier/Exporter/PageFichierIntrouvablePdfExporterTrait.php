<?php

namespace Fichier\Exporter;

use UnicaenApp\Exporter\Pdf;

trait PageFichierIntrouvablePdfExporterTrait
{
    protected PageFichierIntrouvablePdfExporter $pageFichierIntrouvablePdfExporter;

    /**
     * @param \Fichier\Exporter\PageFichierIntrouvablePdfExporter $pageFichierIntrouvablePdfExporter
     */
    public function setPageFichierIntrouvablePdfExporter(PageFichierIntrouvablePdfExporter $pageFichierIntrouvablePdfExporter): void
    {
        $this->pageFichierIntrouvablePdfExporter = $pageFichierIntrouvablePdfExporter;
    }

    /**
     * Génère sur le filesystem un fichier PDF de substitution.
     *
     * @param string $filePath Chemin du fichier introuvable
     * @return string Chemin sur le filesystem du fichier de substitution généré
     */
    protected function generatePageFichierIntrouvable(string $filePath): string
    {
        $data = new PageFichierIntrouvablePdfExporterData($filePath);

        $outputFilepath = sys_get_temp_dir() . '/' . uniqid('sygal_page_fichier_introuvable_') . '.pdf';

        $exporter = clone $this->pageFichierIntrouvablePdfExporter; // clonage indispensable
        $exporter->setVars(['data' => $data]);
        $exporter->export($outputFilepath, Pdf::DESTINATION_FILE);

        return $outputFilepath;
    }
}
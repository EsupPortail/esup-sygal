<?php

namespace RapportActivite\Service\Fichier;

use Application\Command\Exception\TimedOutCommandException;
use Application\Command\ShellCommandRunnerTrait;
use Fichier\Command\Pdf\PdfMergeShellCommandQpdf;
use Application\Command\ShellCommandRunnerTrait;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Service\Fichier\Exporter\PageValidationExportData;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterTrait;
use UnicaenApp\Exporter\Pdf;

class RapportActiviteFichierService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use PageValidationPdfExporterTrait;
    use ShellCommandRunnerTrait;


    /**
     * Génère le fichier du rapport spécifié auquel est ajoutée la page de validation.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @param \RapportActivite\Service\Fichier\Exporter\PageValidationExportData $data
     * @return string
     */
    public function createFileWithPageValidation(RapportActivite $rapport, PageValidationExportData $data): string
    {
        // generation de la page de validation
        $pdvFilePath = $this->generatePageValidation($rapport, $data);

        $outputFilePath = sys_get_temp_dir() . '/' . uniqid('sygal_fusion_rapport_pdv_') . '.pdf';
        $command = $this->createCommandForAjoutPageValidation($rapport, $pdvFilePath, $outputFilePath);
        try {
            $this->runShellCommand($command);
        } catch (TimedOutCommandException $e) {
            // sans timeout, cette exception n'est pas lancée.
        }

        return $outputFilePath;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @param string $pdvFilePath
     * @param string $outputFilePath
     * @return PdfMergeShellCommandQpdf
     */
    private function createCommandForAjoutPageValidation(RapportActivite $rapport, string $pdvFilePath, string $outputFilePath): PdfMergeShellCommandQpdf
    {
        try {
            $rapportFilePath = $this->fichierStorageService->getFileForFichier($rapport->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $rapport->getFichier(), null, $e);
        }

        $command = new PdfMergeShellCommandQpdf();
        $command->setInputFilesPaths([
            0 => $rapportFilePath,
            1 => $pdvFilePath,
        ]);
        $command->setOutputFilePath($outputFilePath);
        $command->generateCommandLine();

        return $command;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @param PageValidationExportData $data
     * @return string
     */
    public function generatePageValidation(RapportActivite $rapport, PageValidationExportData $data): string
    {
        $outputFilepath = sys_get_temp_dir() . '/' . uniqid('sygal_rapport_pdv_') . '.pdf';

        $exporter = clone $this->pageValidationPdfExporter; // clonage indispensable
        $exporter->setVars(['rapport' => $rapport, 'data' => $data]);
        $exporter->export($outputFilepath, Pdf::DESTINATION_FILE);

        return $outputFilepath;
    }

}
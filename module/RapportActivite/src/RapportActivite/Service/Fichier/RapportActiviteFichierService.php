<?php

namespace RapportActivite\Service\Fichier;

use Application\Command\Exception\TimedOutCommandException;
use Application\Command\Pdf\PdfMergeShellCommandQpdf;
use Application\Command\ShellCommandRunnerTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Service\Fichier\Exporter\PageValidationExportData;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;

class RapportActiviteFichierService
{
    use FichierServiceAwareTrait;
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
        // generation de la page de couverture
        $pdcFilePath = tempnam(sys_get_temp_dir(), 'sygal_rapport_pdc_') . '.pdf';
        $this->generatePageValidation($rapport, $data, $pdcFilePath);

        $outputFilePath = tempnam(sys_get_temp_dir(), 'sygal_fusion_rapport_pdc_') . '.pdf';
        $command = $this->createCommandForAjoutPageValidation($rapport, $pdcFilePath, $outputFilePath);
        try {
            $this->runShellCommand($command);
        } catch (TimedOutCommandException $e) {
            // sans timeout, cette exception n'est pas lancée.
        }

        return $outputFilePath;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @param string $pdcFilePath
     * @param string $outputFilePath
     * @return PdfMergeShellCommandQpdf
     */
    private function createCommandForAjoutPageValidation(RapportActivite $rapport, string $pdcFilePath, string $outputFilePath): PdfMergeShellCommandQpdf
    {
        $rapportFilePath = $this->fichierService->computeDestinationFilePathForFichier($rapport->getFichier());
        if (!is_readable($rapportFilePath)) {
            throw new RuntimeException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $rapportFilePath);
        }

        $command = new PdfMergeShellCommandQpdf();
        $command->setInputFilesPaths([
            0 => $rapportFilePath,
            1 => $pdcFilePath,
        ]);
        $command->setOutputFilePath($outputFilePath);
        $command->generateCommandLine();

        return $command;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @param PageValidationExportData $data
     * @param string $filepath
     */
    public function generatePageValidation(RapportActivite $rapport, PageValidationExportData $data, string $filepath)
    {
        $this->pageValidationPdfExporter->setVars(['rapport' => $rapport, 'data' => $data]);
        $this->pageValidationPdfExporter->export($filepath, Pdf::DESTINATION_FILE);
    }

}
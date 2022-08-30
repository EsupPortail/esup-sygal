<?php

namespace Fichier\Command\Pdf;

use Fichier\Command\MergeShellCommand;

/**
 * Commande d'ajout de la page de couverture au manuscrit de thèse.
 *
 * Possibilité de retirer la 1ere page du manuscrit.
 *
 * Version utilisant 'ghostscript'.
 */
class AjoutPdcShellCommandGs extends PdfMergeShellCommandGs
{
    use AjoutPdcShellCommandTrait;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'AjoutPdcShellCommandGs';
    }

    /**
     * Spécifie les 2 fichiers d'entrée : page de couverture et manuscrit.
     *
     * @param array $inputFilesPaths Format attendu : ['couverture' => string, 'manuscrit' => string]
     * @return self
     */
    public function setInputFilesPaths(array $inputFilesPaths): MergeShellCommand
    {
        parent::setInputFilesPaths($inputFilesPaths);
        $this->processInputFilesPaths($inputFilesPaths);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        if ($this->supprimer1erePageDuManuscrit) {
            $this->generateCommandLineAvecSuppression1erePageDuManuscrit();
            return;
        }

        $command = $this->executable . ' ' . $this->noCompressionOption;
        $command .=
            ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $this->outputFilePath .
            ' -dBATCH ' . implode(' ', $this->inputFilesPaths);

        $this->commandLine = $command;
    }

    public function generateCommandLineAvecSuppression1erePageDuManuscrit()
    {
        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid($this->getName() . '_trunc_') . '.pdf';

        $command = $this->executable . ' ' . $this->noCompressionOption;
        $command1 = $command . ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $tmpFilePath . ' -dFirstPage=2 -dBATCH ' . $this->manuscritInputFilePath;
        $command2 = $command . ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $this->outputFilePath . ' -dBATCH ' . $this->couvertureInputFilePath . ' ' . $tmpFilePath;
        $command3 = 'rm -f ' . $tmpFilePath;
        $command = $command1 . ' && ' . $command2 . ' && ' . $command3;

        $this->commandLine = $command;
    }
}
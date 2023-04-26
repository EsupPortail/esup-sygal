<?php

namespace Fichier\Command\Pdf;

use Fichier\Command\MergeShellCommand;

/**
 * Commande de concaténation de N fichiers PDF.
 *
 * Version utilisant 'qpdf'.
 */
class PdfMergeShellCommandQpdf extends MergeShellCommand
{
    protected $executable = 'qpdf';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'MergeShellCommandQpdf';
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        // Commande de fusion (cf. https://qpdf.sourceforge.io/files/qpdf-manual.html#ref.page-selection)
        $command = $this->executable .
            sprintf(' --warning-exit-0 %s --pages . %s -- %s',
                array_shift($this->inputFilesPaths),
                implode(' ', $this->inputFilesPaths),
                $this->outputFilePath
            );

        $this->commandLine = $command;
    }
}
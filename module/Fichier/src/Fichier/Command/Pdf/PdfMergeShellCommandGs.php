<?php

namespace Fichier\Command\Pdf;

use Fichier\Command\MergeShellCommand;

/**
 * Commande de concaténation de N fichiers PDF.
 *
 * Version utilisant 'ghostscript'.
 */
class PdfMergeShellCommandGs extends MergeShellCommand
{
    protected $executable = '/usr/bin/gs';
    protected $noCompressionOption = '-dColorConversionStrategy=/LeaveColorUnchanged -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'PdfMergeShellCommandGs';
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        $command = $this->executable . ' ' . $this->noCompressionOption;
        $command .=
            ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $this->outputFilePath .
            ' -dBATCH ' . implode(' ', $this->inputFilesPaths);

        $this->commandLine = $command;
    }
}
<?php

namespace Retraitement\Filter\Command;

use UnicaenApp\Exception\RuntimeException;

class RetraitementShellCommandMines extends RetraitementShellCommand
{
    const DEFAULT_COMPATIBILITY_LEVEL = '1.4';

    protected $options = [
        'gs_path'             => 'gs',
        'compatibility_level' => '1.4',
    ];

    public function getName(): string
    {
        return 'mines';
    }

    /**
     * @throws RuntimeException En cas de ressources ou pré-requis manquants
     */
    public function checkRequirements()
    {
        $gs = $this->options['gs_path'];

        // test existence de la commande ghostscript
        exec("which $gs", $output, $returnCode);

        if ($returnCode !== 0) {
            throw new RuntimeException("La commande '$gs' semble introuvable");
        }
    }

    public function generateCommandLine()
    {
        $gs = $this->options['gs_path'];
        $level = $this->options['compatibility_level'];

        $errorFilePath  = substr($this->outputFilePath, 0, strlen($this->outputFilePath) - 4) . '_' . $this->getName() . '_error' . '.txt';

        $this->commandLine = <<<EOS
$gs -sDEVICE=pdfwrite\
   -dCompatibilityLevel=$level\
   -dPDFSETTINGS=/printer\
   -dColorConversionStrategy=/LeaveColorUnchanged\
   -dNOPAUSE\
   -dQUIET\
   -dBATCH\
   -sOutputFile="$this->outputFilePath"\
   "$this->inputFilePath" 2>> "$errorFilePath"
EOS;
    }
}
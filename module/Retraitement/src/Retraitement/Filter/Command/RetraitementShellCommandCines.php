<?php

namespace Retraitement\Filter\Command;

use Application\Command\Exception\ShellCommandException;
use UnicaenApp\Exception\RuntimeException;

/**
 * Ligne de commande inspirée de celle fournie par Alexandre Granier (04 67 14 14 14, svp@cines.fr)
 * du CINES pour obtenir le même retraitement que le site https://facile.cines.fr
 *
 * NB: des modifications ont été apportées à la ligne de commande fournie par le CINES pour obtenir
 * quelque chose de potable !
 *
 * @package Retraitement\Filter\Command
 */
class RetraitementShellCommandCines extends RetraitementShellCommand
{
    protected $options = [
        'pdftk_path' => 'pdftk',
        'gs_path' => 'gs',
        'gs_args' => null, // ex: '-dPDFACompatibilityPolicy=1'
    ];

    public function getName(): string
    {
        return 'cines';
    }

    /**
     * Remarques concernant Ghostscript :
     *   -P : Makes Ghostscript look first in the current directory for library files.
     *   -dPDFACompatibilityPolicy=1 n'est pas utilisé car entraîne la perte des liens (cf.
     * http://svn.ghostscript.com/ghostscript/trunk/gs/doc/Ps2pdf.htm#PDFA).
     *   -dSAFER n'est pas utilisé pour ne plus avoir d'erreurs du genre
     *      Substituting font Helvetica-Bold for Arial-BoldMT.
     *          **** Error reading a content stream. The page may be incomplete.
     */
    public function generateCommandLine()
    {
        $dir = __DIR__;

        $errorFilePath  = substr($this->outputFilePath, 0, strlen($this->outputFilePath) - 4) . '_' . $this->getName() . '_error' . '.txt';

        $pdftk = $this->options['pdftk_path'];
        $gs = $this->options['gs_path'];
        $gsArgs = $this->options['gs_args'] ?? '';

        $metadataFilePath = sys_get_temp_dir() . '/' . uniqid('metadata_') . '.txt';
        $tempOutputFilePath = sys_get_temp_dir() . '/' . uniqid('output_') . '.pdf';

        $this->commandLine = <<<EOS
cd $dir\
&&\
$pdftk "$this->inputFilePath" dump_data output "$metadataFilePath" 2> "$errorFilePath"\
&&\
$gs -P\
   -dBATCH\
   -dNOPAUSE\
   -sDEVICE=pdfwrite\
   -dPDFA\
   -dUseCIEColor\
   -sProcessColorModel=DeviceCMYK\
   -sDefaultRGBProfile=AdobeRGB1998.icc\
   -dEmbedAllFonts=true\
   -dSubsetFonts=false\
   -sOutputFile="$tempOutputFilePath"\
   $gsArgs\
   PDFA_def.ps\
   "$this->inputFilePath" 2>> "$errorFilePath"\
&&\
$pdftk "$tempOutputFilePath" update_info "$metadataFilePath" output "$this->outputFilePath" 2>> "$errorFilePath"\
&&\
rm "$tempOutputFilePath" "$metadataFilePath" 
EOS;
    }

    /**
     * @throws \Application\Command\Exception\ShellCommandException En cas de ressources ou prérequis manquants
     */
    public function checkRequirements()
    {
        $dir = __DIR__;

        $filenames = [
            'AdobeRGB1998.icc',
            'PDFA_def.ps',
        ];

        foreach ($filenames as $filename) {
            $filepath = $dir . '/' . $filename;
            if (!file_exists($filepath)) {
                throw new ShellCommandException(sprintf(
                    "Le fichier %s requis doit se trouver dans le même répertoire que la commande %s, à savoir %s.",
                    $filename, $this->getName(), $dir
                ));
            }
            if (!is_readable($filepath)) {
                throw new ShellCommandException(sprintf("Le fichier %s requis n'est pas lisible.", $filepath));
            }
        }
    }
}
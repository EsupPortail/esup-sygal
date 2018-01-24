<?php

namespace Retraitement\Filter\Command;

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
class CinesCommand extends AbstractCommand
{
    protected $options = [
        'pdftk_path' => 'pdftk',
        'gs_path' => 'gs',
        'gs_args' => null, // ex: '-dPDFACompatibilityPolicy=1'
    ];

    public function getName()
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
     *
     * @param      $outputFilePath
     * @param      $inputFilePath
     * @param null $errorOutput
     * @return string
     */
    public function generate($outputFilePath, $inputFilePath, &$errorOutput = null)
    {
        $dir = __DIR__;

        $errorFilePath  = substr($outputFilePath, 0, strlen($outputFilePath) - 4) . '_' . $this->getName() . '_error' . '.txt';

        $pdftk = $this->options['pdftk_path'];
        $gs = $this->options['gs_path'];
        $gsArgs = isset($this->options['gs_args']) ? $this->options['gs_args'] : '';

        $metadataFilePath = sys_get_temp_dir() . '/' . uniqid('metadata_') . '.txt';
        $tempOutputFilePath = sys_get_temp_dir() . '/' . uniqid('output_') . '.pdf';

        $this->commandLine = <<<EOS
cd $dir\
&&\
$pdftk "$inputFilePath" dump_data output "$metadataFilePath" 2> "$errorFilePath"\
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
   "$inputFilePath" 2>> "$errorFilePath"\
&&\
$pdftk "$tempOutputFilePath" update_info "$metadataFilePath" output "$outputFilePath" 2>> "$errorFilePath"\
&&\
rm "$tempOutputFilePath" "$metadataFilePath" 
EOS;

        return $this->commandLine;
    }

    public function execute()
    {
        $this->checkResources();

        return parent::execute();
    }

    /**
     * @throws RuntimeException En cas de ressources ou pré-requis manquants
     */
    public function checkResources()
    {
        $dir = __DIR__;

        $filenames = [
            'AdobeRGB1998.icc',
            'PDFA_def.ps',
        ];

        foreach ($filenames as $filename) {
            $filepath = $dir . '/' . $filename;
            if (!file_exists($filepath)) {
                throw new RuntimeException(sprintf(
                    "Le fichier %s requis doit se trouver dans le même répertoire que la commande %s, à savoir %s.",
                    $filename, $this->getName(), $dir
                ));
            }
            if (!is_readable($filepath)) {
                throw new RuntimeException(sprintf("Le fichier %s requis n'est pas lisible.", $filepath));
            }
        }
    }
}
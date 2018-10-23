<?php

namespace Application\Command;

use UnicaenApp\Exception\RuntimeException;

class GhostscriptCommand {

    protected $executable = 'gs';
    protected $noCompressionOption = '-dColorConversionStrategy=/LeaveColorUnchanged -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode';

    /**
     * @param string $couverture
     * @param string $corps
     * @param string $output
     */
    public function merge($couverture, $corps, $output)
    {
        $command  = $this->executable . ' '. $this->noCompressionOption;
        $command .= ' '. '-dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE='.$output.' -dBATCH '.$couverture.' '.$corps;
        $output = [];
        $return = null;
        exec($command, $output, $return);
        if ($return !== 0) {
            $msg  = 'valeur de retour : '. $return . '<br>';
            $msg .= 'sortie : <br/>';
            foreach ($output as $line) {
                $msg .= $line . '<br/>';
            }
            throw new RuntimeException("Un problème s'est produit lors de la concaténation de la page de couverture et du manuscrit. <br/>" . $msg);
        }
    }

    /**
     * @param string $couverture
     * @param string $corps
     * @param string $output
     */
    public function removeThenMerge($couverture, $corps, $output)
    {
        $command  = $this->executable . ' '. $this->noCompressionOption;
        $command .= ' '. '-dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE='.$output.' -dBATCH '.$couverture.' -dFirstPage=2 -dBATCH '.$corps;
        $output = [];
        $return = null;
        exec($command, $output, $return);
        if ($return !== 0) {
            $msg  = 'valeur de retour : '. $return . '<br>';
            $msg .= 'sortie : <br/>';
            foreach ($output as $line) {
                $msg .= $line . '<br/>';
            }
            throw new RuntimeException("Un problème s'est produit lors de la concaténation de la page de couverture et du manuscrit. <br/>" . $msg);
        }
    }
}
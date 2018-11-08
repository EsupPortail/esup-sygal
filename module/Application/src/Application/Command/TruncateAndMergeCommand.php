<?php

namespace Application\Command;

use Assert\Assertion;
use Assert\AssertionFailedException;
use UnicaenApp\Exception\LogicException;

class TruncateAndMergeCommand extends AbstractCommand {

    protected $executable = 'gs';
    protected $noCompressionOption = '-dColorConversionStrategy=/LeaveColorUnchanged -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode';

    /**
     * @return string
     */
    public function getName()
    {
        return 'TruncateAndMerge';
    }

    /**
     * @param string $outputFilePath
     * @param array $inputFilePath
     * @param string $errorFilePath
     * @return string
     */
    public function generate($outputFilePath, $inputFilePath, &$errorFilePath = null)
    {
        try {
            Assertion::keyIsset($inputFilePath, 'couverture');
            Assertion::keyIsset($inputFilePath, 'manuscrit');
        } catch (AssertionFailedException $e) {
            throw new LogicException("Argument invalide");
        }

        $couverture = $inputFilePath['couverture'];
        $manuscrit = $inputFilePath['manuscrit'];

        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid() . '.pdf';

        $command = $this->executable . ' ' . $this->noCompressionOption;
//        $command .= ' ' . '-dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $outputFilePath . ' -dBATCH ' . $couverture . ' -dFirstPage=2 -dBATCH ' . $manuscrit;
        $command1 = $command . ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $tmpFilePath . ' -dFirstPage=2 -dBATCH ' . $manuscrit;
        $command2 = $command . ' -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $outputFilePath . ' -dBATCH ' . $couverture . ' ' . $tmpFilePath;
        $command = $command1 . ' && ' . $command2;

        $this->commandLine = $command;

        return $this->commandLine;
    }
}
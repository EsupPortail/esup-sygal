<?php

namespace Application\Command;

use Assert\Assertion;
use Assert\AssertionFailedException;
use UnicaenApp\Exception\LogicException;

class MergeCommand extends AbstractCommand {

    protected $executable = 'gs';
    protected $noCompressionOption = '-dColorConversionStrategy=/LeaveColorUnchanged -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode';

    /**
     * @return string
     */
    public function getName()
    {
        return 'merge';
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

        $command = $this->executable . ' ' . $this->noCompressionOption;
        $command .= ' ' . '-dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' . $outputFilePath . ' -dBATCH ' . $couverture . ' ' . $manuscrit;

        $this->commandLine = $command;

        return $this->commandLine;
    }
}
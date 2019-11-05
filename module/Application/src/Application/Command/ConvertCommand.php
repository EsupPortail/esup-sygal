<?php

namespace Application\Command;

use Assert\Assertion;
use Assert\AssertionFailedException;
use UnicaenApp\Exception\LogicException;

class ConvertCommand extends AbstractCommand {

    protected $executable = 'convert';

    /**
     * @return string
     */
    public function getName()
    {
        return 'convert';
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
            Assertion::keyIsset($inputFilePath, 'logo');
        } catch (AssertionFailedException $e) {
            throw new LogicException("Argument invalide");
        }

        $logo = $inputFilePath['logo'];

        $command = $this->executable . ' \'' . $logo . '\' \''. $outputFilePath . '\'';
        $this->commandLine = $command;

        return $this->commandLine;
    }
}
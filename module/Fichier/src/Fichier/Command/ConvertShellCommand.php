<?php

namespace Fichier\Command;

use Application\Command\ShellCommand;

class ConvertShellCommand extends ShellCommand
{
    protected $executable = 'convert';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'convert';
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        $command = $this->executable . sprintf(" '%s' '%s' 2>&1", $this->inputFilePath, $this->outputFilePath);

        $this->commandLine = $command;
    }
}
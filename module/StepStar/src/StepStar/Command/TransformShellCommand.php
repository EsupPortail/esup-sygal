<?php

namespace StepStar\Command;

use Application\Command\ShellCommand;

/**
 * Transformation XSL utilisant l'utilitaire en ligne de commande 'transform' de SaxonC,
 * utilitaire qui doit être buildée et dispo dans le PATH du serveur.
 *
 * @see https://www.saxonica.com/saxon-c/documentation11/index.html#!starting/running (Command line interface)
 */
class TransformShellCommand extends ShellCommand
{
    protected string $executable = 'transform';
    protected bool $verbose = false;
    protected string $xslFilePath;

    /**
     * @param string $xslFilePath
     * @return self
     */
    public function setXslFilePath(string $xslFilePath): self
    {
        $this->xslFilePath = $xslFilePath;
        return $this;
    }

    /**
     * @param bool $verbose
     * @return self
     */
    public function setVerbose(bool $verbose = true): self
    {
        $this->verbose = $verbose;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'transform';
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        $parts = [
            $this->executable,
            $this->verbose ? '-t' : null,
            sprintf("-s:'%s' -xsl:'%s' -o:'%s' 2>&1", $this->inputFilePath, $this->xslFilePath, $this->outputFilePath),
        ];

        $this->commandLine = implode(' ', array_filter($parts));
    }
}
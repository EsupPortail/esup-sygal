<?php

namespace Fichier\Command;

use Application\Command\ShellCommand;

/**
 * Commande de concaténation de 2 fichiers.
 */
abstract class MergeShellCommand extends ShellCommand
{
    protected $executable;

    /**
     * @var string[]
     */
    protected $inputFilesPaths = [];

    /**
     * Spécifie les chemins des fichiers à concaténer, dans l'ordre.
     *
     * @param array $inputFilesPaths
     * @return self
     */
    public function setInputFilesPaths(array $inputFilesPaths): self
    {
        $this->inputFilesPaths = $inputFilesPaths;
        return $this;
    }
}
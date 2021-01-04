<?php

namespace UnicaenDeploy;

use RuntimeException;

/**
 * Lanceur de commande shell.
 *
 * @author Unicaen
 */
class ShellCommandRunner
{
    /**
     * @var boolean
     */
    private $async = false;

    /**
     * @var boolean
     */
    private $dryRun = false;

    /**
     * @param bool $async
     * @return self
     */
    public function setAsync($async = true)
    {
        $this->async = $async;

        return $this;
    }

    /**
     * @param bool $dryRun
     * @return ShellCommandRunner
     */
    public function setDryRun(bool $dryRun = true): ShellCommandRunner
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * Lance la commande spécifiée.
     *
     * @param string $commandString
     * @return string
     */
    public function run(string $commandString)
    {
        $command = $commandString;

        if ($this->async) {
            $command = 'nohup ' . $command . ' > /dev/null 2>&1 &';
        }

        if ($this->dryRun) {
            return null;
        }

        // exécution de la commande
        exec($command, $output, $returnCode);

        if ($this->async) {
            return null;
        }

        if (!is_array($output) || !isset($output[0])) {
            throw new RuntimeException(
                sprintf("La ligne de commande '%s' n'a retourné aucun résultat.", $command));
        }

        return trim($output[0]);
    }
}
<?php

namespace Application\Command;

use RuntimeException;

/**
 * Lanceur de commande shell.
 *
 * @author Unicaen
 */
class ShellCommandRunner
{
    /**
     * @var string Commande à exécuter.
     */
    protected $commandString;

    /**
     * @var boolean
     */
    private $async = false;

    /**
     * Constructor.
     *
     * @param string $commandString Commande à exécuter.
     */
    function __construct($commandString)
    {
        $this->commandString = $commandString;
    }

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
     * Lance la commande.
     *
     * @return string
     */
    public function run()
    {
        $command = $this->commandString;

        if ($this->async) {
            $command = 'nohup ' . $command . ' > /dev/null 2>&1 &';
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

    /**
     * @return string
     */
    public function getCommandString()
    {
        return $this->commandString;
    }
}
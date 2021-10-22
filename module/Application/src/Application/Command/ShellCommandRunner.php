<?php

namespace Application\Command;

use Application\Command\Exception\TimedOutCommandException;
use InvalidArgumentException;

/**
 * Lanceur de commande shell, spécifiée :
 * - soit au format {@see \Application\Command\ShellCommandInterface} avec {@see setCommand()} ;
 * - soit au format string avec {@see setCommandAsString()}.
 *
 * Trois façons de lancer une commande :
 * - lancement classique : {@see runCommand()} ;
 * - lancement en arrière-plan : {@see runCommandInBackground()} ;
 * - lancement avec limite de temps : {@see runCommandWithTimeout()}.
 *
 * @author Unicaen
 */
class ShellCommandRunner
{
    /**
     * @var \Application\Command\ShellCommandInterface Commande à exécuter.
     */
    protected $command;

    /**
     * @var string Commande à exécuter, spécifiée sous forme de string.
     */
    protected $commandAsString;

    /**
     * @var int|null
     */
    protected $returnCode;

    /**
     * @var array
     */
    protected $output;

    /**
     * @param \Application\Command\ShellCommandInterface $command
     * @return $this
     */
    public function setCommand(ShellCommandInterface $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @param string $commandAsString
     * @return $this
     */
    public function setCommandAsString(string $commandAsString): self
    {
        $this->commandAsString = $commandAsString;

        return $this;
    }

    /**
     * @return string
     */
    protected function prepareCommandLine(): string
    {
        if ($this->command !== null) {
            $commandLine = $this->command->getCommandLine();
            if (!$commandLine) {
                throw new InvalidArgumentException(sprintf(
                    "La ligne de commande n'a pas été générée, appelez %s::generateCommandLine() auparavant.",
                    get_class($this->command)
                ));
            }
        } elseif ($this->commandAsString !== null) {
            $commandLine = $this->commandAsString;
        } else {
            throw new InvalidArgumentException("Aucune commande spécifiée.");
        }

        return $commandLine;
    }

    /**
     * Lance la commande de façon classique.
     *
     * @return \Application\Command\ShellCommandResultInterface
     */
    public function runCommand(): ShellCommandResultInterface
    {
        $commandLine = $this->prepareCommandLine();

        // exécution de la commande
        exec($commandLine, $output, $returnCode);

        $this->returnCode = $returnCode;
        $this->output = $output;

        return $this->createRunCommandResult();
    }

    /**
     * Lance la commande en arrière-plan (nohup + &).
     *
     * Du coup, pas de collecte de log ni de code de retour.
     */
    public function runCommandInBackground()
    {
        $commandLine = $this->prepareCommandLine();
        $commandLine = 'nohup ' . $commandLine . ' > /dev/null 2>&1 &';

        // exécution de la commande
        exec($commandLine);
    }

    /**
     * Lance la commande avec une limite de temps (timeout).
     *
     * Si la limite de temps est atteinte, la commande est stoppée (signal HUP) et
     * une exception {@see TimedOutCommandException} est levée.
     *
     * @param string $timeout Ex: '60s', '1m', '2h', '1d'. Cf. "man timeout".
     * @return \Application\Command\ShellCommandResultInterface
     * @throws \Application\Command\Exception\TimedOutCommandException
     */
    public function runCommandWithTimeout(string $timeout): ShellCommandResultInterface
    {
        $commandLine = $this->prepareCommandLine();
        $commandLine = "timeout --signal=HUP $timeout " . $commandLine;

        exec($commandLine, $output, $returnCode);

        // un code retour 124 indique que la commande a été exécutée avec un timeout et que ce timeout a été atteint
        if ($timeout && $returnCode === 124) {
            $toce = new TimedOutCommandException();
            $toce->setTimeout($timeout);
            throw $toce;
        }

        $this->returnCode = $returnCode;
        $this->output = $output;

        return $this->createRunCommandResult();
    }

    protected function createRunCommandResult(): ShellCommandResultInterface
    {
        if ($this->command !== null) {
            // résultat construit par la ShellCommand
            return $this->command->createResult($this->output, $this->returnCode);
        } elseif ($this->commandAsString !== null) {
            // résultat générique
            return new ShellCommandResult($this->output, $this->returnCode);
        } else {
            throw new InvalidArgumentException("Aucune commande spécifiée.");
        }
    }

    /**
     * Retourne les logs de sortie éventuels.
     *
     * @return array
     * @deprecated
     */
    public function getOutput(): array
    {
        return $this->output;
    }

}
<?php

namespace Application\Command;

use UnicaenApp\Exception\RuntimeException;

trait ShellCommandRunnerTrait
{
    /**
     * @throws \Application\Command\Exception\TimedOutCommandException
     */
    protected function runShellCommand(ShellCommandInterface $command, string $timeout = null)
    {
        $runner = new ShellCommandRunner();
        $runner->setCommand($command);
        try {
            if ($timeout) {
                $result = $runner->runCommandWithTimeout($timeout);
            } else {
                $result = $runner->runCommand();
            }

            if (!$result->isSuccessfull()) {
                $message = sprintf("La commande '%s' a échoué (code retour = %s) : %s",
                    $command->getName(),
                    $result->getReturnCode(),
                    $command->getCommandLine()
                );
                if ($output = $result->getOutput()) {
                    $message .= "Voici le log d'exécution : " . implode(PHP_EOL, $output);
                }
                throw new RuntimeException($message);
            }
        }
        catch (RuntimeException $rte) {
            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande " . $command->getName(),
                0,
                $rte);
        }

    }
}
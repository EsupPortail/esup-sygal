<?php

namespace Retraitement\Service;

use Application\Command\Exception\TimedOutCommandException;
use Application\Command\ShellCommandRunner;
use Retraitement\Filter\Command\RetraitementShellCommand;
use RuntimeException;

class RetraitementService
{
    /**
     * RetraitementService constructor.
     *
     * @param RetraitementShellCommand $command
     */
    public function __construct(RetraitementShellCommand $command)
    {
        $this->setCommand($command);
    }

    /**
     * @var RetraitementShellCommand
     */
    private $command;

    /**
     * @param RetraitementShellCommand $command
     * @return self
     */
    public function setCommand(RetraitementShellCommand $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Crée un fichier retraité à partir d'un fichier sur le disque.
     *
     * @param string $inputFilePath Chemin du fichier à retraiter
     * @param string $outputFilePath Chemin du fichier retraité généré
     * @param string|null $timeout Timeout à appliquer au lancement du script de retraitement.
     * @throws TimedOutCommandException Le timout d'exécution a été atteint
     */
    private function retraiterFichierByPath(string $inputFilePath, string $outputFilePath, string $timeout = null)
    {
        $this->command->setOutputFilePath($outputFilePath);
        $this->command->setInputFilePath($inputFilePath);
        $this->command->generateCommandLine();

        $runner = new ShellCommandRunner();
        $runner->setCommand($this->command);
        try {
            if ($timeout) {
                $result = $runner->runCommandWithTimeout($timeout);
            } else {
                $result = $runner->runCommand();
            }

            if (!$result->isSuccessfull()) {
                $message = sprintf("La commande '%s' a échoué (code retour = %s). ",
                    $this->command->getName(),
                    $result->getReturnCode()
                );
                if ($output = $result->getOutput()) {
                    $message .= "Voici le log d'exécution : " . implode(PHP_EOL, $output);
                }
                throw new RuntimeException($message);
            }
        }
        catch (RuntimeException $rte) {
            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande de retraitement " . $this->command->getName(),
                0,
                $rte);
        }
    }

    /**
     * Crée un fichier retraité à partir du Fichier spécifié.
     *
     * @param string $inputFilePath Chemin sur le disque vers le fichier à retraiter
     * @param string $outputFilePath Chemin du fichier retraité généré
     * @param string|null $timeout  Timeout à appliquer au lancement du script de retraitement.
     * @throws TimedOutCommandException Le timout a été atteint
     */
    public function retraiterFichier(string $inputFilePath, string $outputFilePath, string $timeout = null)
    {
        $this->retraiterFichierByPath($inputFilePath, $outputFilePath, $timeout);
    }
}
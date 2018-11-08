<?php

namespace Retraitement\Service;

use Retraitement\Exception\TimedOutCommandException;
use Application\Command\CommandInterface;
use RuntimeException;

class RetraitementService
{
    /**
     * FichierStarCorrector constructor.
     *
     * @param \Application\Filter\Command\\Application\Command\CommandInterface $command
     */
    public function __construct(CommandInterface $command)
    {
        $this->setCommand($command);
    }

    /**
     * @var \Application\Filter\Command\\Application\Command\CommandInterface
     */
    private $command;

    /**
     * @param \Application\Filter\Command\CommandInterface $command
     * @return self
     */
    public function setCommand(CommandInterface $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Crée un fichier retraité à partir d'un fichier sur le disque.
     *
     * @param string $inputFilePath Chemin du fichier à retraiter
     * @param string $outputFilePath Chemin du fichier retraité généré
     * @param string  $timeout Timeout à appliquer au lancement du script de retraitement.
     * @throws TimedOutCommandException Le timout a été atteint
     */
    private function retraiterFichierByPath($inputFilePath, $outputFilePath, $timeout = null)
    {
        $this->command->generate($outputFilePath, $inputFilePath, $errorFilePath);
        if ($timeout) {
            $this->command->setOption('timeout', $timeout);
        }
        try {
            $this->command->checkResources();
            $this->command->execute();

            $success = ($this->command->getReturnCode() === 0);
            if (!$success) {
                throw new RuntimeException(sprintf(
                    "La commande %s a échoué (code retour = %s), voici le résultat d'exécution : %s",
                    $this->command->getName(),
                    $this->command->getReturnCode(),
                    implode(PHP_EOL, $this->command->getResult())
                ));
            }
        }
        catch (TimedOutCommandException $toce) {
            throw $toce;
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
     * @param string $timeout  Timeout à appliquer au lancement du script de retraitement.
     * @throws TimedOutCommandException Le timout a été atteint
     */
    public function retraiterFichier($inputFilePath, $outputFilePath, $timeout = null)
    {
        $this->retraiterFichierByPath($inputFilePath, $outputFilePath, $timeout);
    }
}
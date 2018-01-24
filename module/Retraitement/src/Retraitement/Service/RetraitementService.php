<?php

namespace Retraitement\Service;

use Application\Entity\Db\Fichier;
use Retraitement\Filter\Command\CommandInterface;
use RuntimeException;

class RetraitementService
{
    /**
     * FichierStarCorrector constructor.
     *
     * @param CommandInterface $command
     */
    public function __construct(CommandInterface $command)
    {
        $this->setCommand($command);
    }

    /**
     * @var CommandInterface
     */
    private $command;

    /**
     * @param CommandInterface $command
     * @return self
     */
    public function setCommand(CommandInterface $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @param Fichier $fichier
     * @return string Chemin du fichier généré
     */
    public function retraiterFichier(Fichier $fichier)
    {
        // création du fichier d'entrée sur le disque
        $inputFilePath = $fichier->writeFichierToDisk();

        $outputFilePath = $this->generateOutputFilePath($inputFilePath);
        $this->command->generate($outputFilePath, $inputFilePath, $errorFilePath);
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
        catch (RuntimeException $rte) {
            // suppression du fichier d'entrée sur le disque
            unlink($inputFilePath);

            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande de retraitement " . $this->command->getName(),
                0,
                $rte);
        }

        // suppression du fichier d'entrée sur le disque
        unlink($inputFilePath);

        return $outputFilePath;
    }

    private function generateOutputFilePath($inputFilePath)
    {
        $parts = pathinfo($inputFilePath);
        $dirname = $parts['dirname'];
        $filename = $parts['filename'];
        $extension = $parts['extension'];

        $outputFilePath = "";
        if ($dirname) {
            $outputFilePath .= $dirname . '/';
        }
        $outputFilePath .= uniqid($filename . '-');
        $outputFilePath .= '.' . $extension;

        return $outputFilePath;
    }
}
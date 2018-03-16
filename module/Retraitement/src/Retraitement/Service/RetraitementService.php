<?php

namespace Retraitement\Service;

use Application\Entity\Db\Fichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Retraitement\Exception\TimedOutCommandException;
use Retraitement\Filter\Command\CommandInterface;
use RuntimeException;

class RetraitementService
{
    use FichierServiceAwareTrait;

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
     * Crée un fichier retraité à partir d'un fichier sur le disque.
     *
     * @param string $inputFilePath Chemin du fichier à retraiter
     * @param string  $timeout Timeout à appliquer au lancement du script de retraitement.
     * @return string Chemin du fichier retraité généré
     * @throws TimedOutCommandException Le timout a été atteint
     */
    public function retraiterFichierByPath($inputFilePath, $timeout = null)
    {
        $outputFilePath = $this->generateOutputFilePath($inputFilePath);
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
            // suppression du fichier d'entrée sur le disque
            unlink($inputFilePath);

            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande de retraitement " . $this->command->getName(),
                0,
                $rte);
        }

        return $outputFilePath;
    }

    /**
     * Crée un fichier retraité à partir du Fichier spécifié.
     *
     * @param Fichier $fichier Fichier à retraiter
     * @param string  $timeout Timeout à appliquer au lancement du script de retraitement.
     * @return string Chemin du fichier retraité généré
     * @throws TimedOutCommandException Le timout a été atteint
     */
    public function retraiterFichier(Fichier $fichier, $timeout = null)
    {
        $inputFilePath = $this->fichierService->writeFichierToDisk($fichier);
        $outputFilePath = $this->retraiterFichierByPath($inputFilePath, $timeout);
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
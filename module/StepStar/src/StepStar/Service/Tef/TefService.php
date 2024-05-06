<?php

namespace StepStar\Service\Tef;

use Application\Command\ShellCommandRunner;
use StepStar\Command\TransformShellCommand;
use StepStar\Exception\TefServiceException;
use UnicaenApp\Exception\RuntimeException;

class TefService
{
    /**
     * @var string
     */
    protected string $outputDir;

    /**
     * TefService constructor.
     */
    public function __construct()
    {
        $this->outputDir = sys_get_temp_dir();
    }

    /**
     * @param string $outputDir
     * @throws \StepStar\Exception\TefServiceException
     */
    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;

        if (file_exists($this->outputDir)) {
            throw new TefServiceException("Le répertoire destination spécifié existe déjà : " . $this->outputDir);
        }

        mkdir($this->outputDir, 0777, true);
    }

    /**
     * Transforme, à l'aide d'un fichier XSL, les fichiers XML placés dans un répertoire, pour produire les fichiers TEF.
     *
     * NB : ne requiert pas le module PHP SaxonC mais l'utilitaire en ligne de commande 'transform' de SaxonC, cf.
     * {@see TransformShellCommand}
     *
     * @param string $inputDirPath
     * @param string $xslFilePath
     * @throws \StepStar\Exception\TefServiceException
     */
    public function generateTefFilesFromDir(string $inputDirPath, string $xslFilePath): void
    {
        $command = new TransformShellCommand();
        $command->setInputFilePath($inputDirPath);
        $command->setXslFilePath($xslFilePath);
        $command->setOutputFilePath($this->outputDir);
        $command->generateCommandLine();

        $runner = new ShellCommandRunner();
        $runner->setCommand($command);
        try {
            $result = $runner->runCommand();

            if (!$result->isSuccessfull()) {
                $message = sprintf("La commande '%s' a échoué (code retour = %s). ",
                    $command->getCommandLine(),
                    $result->getReturnCode()
                );
                if ($output = $result->getOutput()) {
                    $message .= "Voici le log d'exécution : " . implode(PHP_EOL, $output);
                }
                throw new RuntimeException($message);
            }
        } catch (RuntimeException $rte) {
            throw new TefServiceException(
                "Une erreur est survenue lors de l'exécution de la commande " . $command->getName(),
                0,
                $rte);
        }
    }
}
<?php

namespace StepStar\Service\Tef;

use Application\Command\ShellCommandRunner;
use StepStar\Command\TransformShellCommand;
use StepStar\Exception\TefServiceException;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xslt\XsltServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class TefService
{
    use XmlServiceAwareTrait;
    use XsltServiceAwareTrait;

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
     * @param string $inputDirPath
     * @param string $xslFilePath
     * @throws \StepStar\Exception\TefServiceException
     */
    public function generateTefFilesFromDir(string $inputDirPath, string $xslFilePath)
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

    /**
     * Transforme, à l'aide d'un fichier XSL, le fichier XML spécifié, pour produire les fichiers TEF.
     *
     * @param string $xmlFilePath
     * @param string $xslFilePath
     * @throws \StepStar\Exception\TefServiceException
     */
    public function generateTefFilesFromFile(string $xmlFilePath, string $xslFilePath)
    {
        if ($this->outputDir === null) {
            throw new TefServiceException("Aucun répertoire destination n'a été spécifié");
        }

        // transformation XSLT
        $this->xsltService
            ->setOutputDir($this->outputDir)
            ->setXslFilePath($xslFilePath)
            ->transformToFiles($xmlFilePath);
    }
}
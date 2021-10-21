<?php

namespace Retraitement;

use Application\Command\ShellCommandRunner;
use Application\Validator\FichierCinesValidator;
use Retraitement\Filter\Command\RetraitementShellCommandCines;
use Retraitement\Filter\Command\RetraitementShellCommandMines;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Filter\BytesFormatter;

/**
 * Class RetraitValid
 *
 * @package RetraitValid
 */
class RetraitValid
{
    const COMMAND_CINES = 'cines';
    const COMMAND_MINES = 'mines';

    const CSV_DELIMITER = '|';

    const XML_TAG_VALID = FichierCinesValidator::XML_TAG_VALID;
    const XML_TAG_WELLFORMED = FichierCinesValidator::XML_TAG_WELLFORMED;
    const XML_TAG_ARCHIVABLE = FichierCinesValidator::XML_TAG_ARCHIVABLE;
    const XML_TAG_MESSAGE = FichierCinesValidator::XML_TAG_MESSAGE;
    const XML_TAG_SIZE = FichierCinesValidator::XML_TAG_SIZE;
    const XML_TAG_SHA256SUM = FichierCinesValidator::XML_TAG_SHA256SUM;
    const XML_TAG_FORMAT = FichierCinesValidator::XML_TAG_FORMAT;
    const XML_TAG_VERSION = FichierCinesValidator::XML_TAG_VERSION;

    private $inputFilePath;
    private $csvOutputFilePath;
    private $outputDir;

    private $csvInputFileHandle;
    private $csvOutputFileHandle;

    /**
     * @var FichierCinesValidator
     */
    private $fichierCinesValidator;

    /**
     * @param FichierCinesValidator $fichierCinesValidator
     */
    public function __construct(FichierCinesValidator $fichierCinesValidator)
    {
        $this->fichierCinesValidator = $fichierCinesValidator;
    }

    protected function __destruct()
    {
        if ($this->csvOutputFileHandle) {
            fclose($this->csvOutputFileHandle);
        }
    }

    /**
     * Point d'entrée.
     *
     * @param string $inputFilePath Chemin vers le répertoire à parcourir, ou vers le fichier CSV à lire
     * @param string $outputDir     Chemin vers le répertoire où seront créés les fichiers retraités et le fichiers CSV de log
     */
    public function execute($inputFilePath, $outputDir)
    {
        $this->inputFilePath = $inputFilePath;
        $this->outputDir = $outputDir;
        $this->csvOutputFilePath = $outputDir . '/resultats.csv';

        chdir(__DIR__);

        try {
            $this->load();
            exit(0);
        } catch (RuntimeException $e) {
            echo PHP_EOL;
            echo "Une erreur est survenue : " . PHP_EOL . $e->getMessage();
            echo PHP_EOL;
            echo PHP_EOL;
            exit(1);
        }
    }

    private function getFilesData()
    {
        if (is_file($this->inputFilePath)) {
            return $this->getFilesDataFromCsv();
        }

        return $this->getFilesDataFromDir();
    }


    private function getFilesDataFromCsv()
    {
        echo "Source : fichier CSV '$this->inputFilePath'" . PHP_EOL . PHP_EOL;

        $data = [];

        // la 1ere ligne du fichier doit contenir les entêtes de colonnes requises
        $cols = fgetcsv($h = $this->getCsvInputFileHandle(), null, self::CSV_DELIMITER);

        $expectedCols = [
            'enabled'   => "Flag indiquant si le fichier doit être traiter (0 ou 1)",
            'directory' => "Répertoire où se trouve le fichier",
            'filename'  => "Nom du fichier avec extension",
            'command'   => "Code de la commande de retraitement à appliquer",
        ];
        if (array_keys($expectedCols) !== $cols) {
            throw new RuntimeException(
                "Le fichier d'entrée CSV doit posséder les entêtes de colonnes suivantes (ni plus ni moins et dans l'ordre) : " . PHP_EOL .
                implode(', ', array_map(function ($name, $desc) { return "  - $name : $desc"; }, array_keys($expectedCols), $expectedCols))
            );
        }

        // lecture du fichier d'entrée ligne par ligne
        while (($row = fgetcsv($h, 1000, self::CSV_DELIMITER)) !== FALSE) {
            $data[] = [
                'enabled'   => $row[0],
                'directory' => $row[1],
                'filename'  => $row[2],
                'command'   => $row[3],

                'filepath'  => $row[1] . '/' . $row[2],
            ];
        }

        fclose($h);

        return $data;
    }

    private function getFilesDataFromDir()
    {
        echo "Source : répertoire à parcourir '$this->inputFilePath'" . PHP_EOL . PHP_EOL;

        $iterator = new \RecursiveDirectoryIterator($this->inputFilePath);
        //$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        // could use CHILD_FIRST if you so wish

        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() == "pdf") {
                $files[$file->getFilename()] = [
                    'filename' => $file->getFilename(),
                    'filepath' => $this->inputFilePath . '/' . $file->getFilename(),
                    'filesize' => $file->getSize(),
                ];
            }
        }

        ksort($files); // tri par nom de fichier

        return $files;
    }

    public function load()
    {
        if (file_exists($this->csvOutputFilePath)) {
            throw new RuntimeException("Le fichier résultat '$this->csvOutputFilePath' existe déjà");
        }

        // écriture des entêtes de colonnes dans le fichier des résultats
        fputcsv($this->getCsvOutputFileHandle(), [
            "Fichier",
            "Taille AVANT",
            "Valide AVANT",
            "Bien formé AVANT",
            "Archivable AVANT",
            "Format identifié AVANT",
            "Retraitement",
            "Durée",
            "Taille APRÈS",
            "Valide APRÈS",
            "Bien formé APRÈS",
            "Archivable APRÈS",
            "Format identifié APRÈS",
            "Résultat validation AVANT",
            "Messages validation AVANT",
            "Résultat validation APRÈS",
            "Messages validation APRÈS",
        ], self::CSV_DELIMITER);

        $precFileName = null;
        $resutatValidAvantRetraitement = null;
        $f = new BytesFormatter();

        foreach ($this->getFilesData() as $row) {

            $filename = $row['filename'];
            $filepath = $row['filepath'];
            $filesize = array_key_exists('filesize', $row) ? $row['filesize'] : filesize($filepath);
            $enabled = array_key_exists('enabled', $row) ? (bool)$row['enabled'] : true;
            $command = array_key_exists('command', $row) ? $row['command'] : self::COMMAND_MINES;

            if (!$enabled) {
                continue;
            }

            echo sprintf("%s (%s)", $filename, $f->filter($filesize)) . PHP_EOL;

            if (!file_exists($filepath) || !is_readable($filepath)) {
                throw new RuntimeException("Le fichier '$filepath' n'existe pas ou n'est pas lisible");
            }

            // petite optimisation
            if ($precFileName !== $filename) {
                $resutatValidAvantRetraitement = $this->validerFichier($filepath);
                $resutatValidAvantRetraitement['resultat'] = $this->fichierCinesValidator->getResult();
            }

            // résultat
            $resultat = [];
            $resultat[] = $filename;
            $resultat[] = $f->filter($filesize);
            $resultat[] = $resutatValidAvantRetraitement[self::XML_TAG_VALID] ? 'O' : 'N';
            $resultat[] = $resutatValidAvantRetraitement[self::XML_TAG_WELLFORMED] ? 'O' : 'N';
            $resultat[] = $resutatValidAvantRetraitement[self::XML_TAG_ARCHIVABLE] ? 'O' : 'N';
            $resultat[] = $resutatValidAvantRetraitement[self::XML_TAG_FORMAT] . " " . $resutatValidAvantRetraitement[self::XML_TAG_VERSION];

            $estUnPdf = strtolower($resutatValidAvantRetraitement[self::XML_TAG_FORMAT]) === 'pdf';

            // le retraitement ne concerne que les PDF
            if ($estUnPdf) {
                $outputFilePath = $this->outputDir . '/' . substr($filename, 0, strlen($filename) - 4) . '_' . $command . '.pdf';
                $errorFilePath = $this->outputDir . '/' . substr($filename, 0, strlen($filename) - 4) . '_' . $command . '_error' . '.txt';

                $resultRetraitement = $this->retraiterFichier($command, $filepath, $outputFilePath, $errorFilePath);

                if (isset($resultRetraitement['error'])) {
                    echo $resultRetraitement['error'] . PHP_EOL;
                }

                $resutatValidApresRetraitement = $this->validerFichier($outputFilePath);
                $resutatValidApresRetraitement['resultat'] = $this->fichierCinesValidator->getResult();

                // résultat
                $resultat[] = $command;
                $resultat[] = $resultRetraitement['duration'];
                $resultat[] = $f->filter($resultRetraitement['filesize']);
                $resultat[] = $resutatValidApresRetraitement[self::XML_TAG_VALID] ? 'O' : 'N';
                $resultat[] = $resutatValidApresRetraitement[self::XML_TAG_WELLFORMED] ? 'O' : 'N';
                $resultat[] = $resutatValidApresRetraitement[self::XML_TAG_ARCHIVABLE] ? 'O' : 'N';
                $resultat[] = $resutatValidApresRetraitement[self::XML_TAG_FORMAT] . " " . $resutatValidApresRetraitement[self::XML_TAG_VERSION];

                $resultat[] = $resutatValidAvantRetraitement['resultat'];
                $resultat[] = $resutatValidAvantRetraitement[self::XML_TAG_MESSAGE];
                $resultat[] = $resutatValidApresRetraitement['resultat'];
                $resultat[] = $resutatValidApresRetraitement[self::XML_TAG_MESSAGE];
            }
            $this->saveLigneResultat($resultat);

            echo PHP_EOL;

            $precFileName = $filename;
        }
    }

    /**
     * @param string $commandName
     * @param string $inputFilePath
     * @param string $outputFilePath
     * @param string $errorFilePath
     * @return array
     */
    private function retraiterFichier($commandName, $inputFilePath, $outputFilePath, $errorFilePath)
    {
        $command = $this->getRetraitementCommand($commandName);

        echo "  - Retraitement $commandName... ";

        $start = microtime(true);
        $command->generateCommandLine($outputFilePath, $inputFilePath, $errorFilePath);
        $commandLine = $command->getCommandLine();
        $runner = new ShellCommandRunner();
        $runner->setCommand($command);
        $result = $runner->runCommand();
        $duration = round(microtime(true) - $start, 2);

        echo sprintf("(%s secondes) : ", $duration);
        echo '"' . $outputFilePath . '"';
        echo PHP_EOL;

        if (file_exists($errorFilePath) && !file_get_contents($errorFilePath)) {
            unlink($errorFilePath);
        }

        $error = null;
        if (!$result->isSuccessfull()) {
            $error = "La commande de retraitement suivante a renvoyé une erreur: " . $commandLine . PHP_EOL .
                "Faites ceci pour en savoir plus: cat \"$errorFilePath\"";
        }
        if (!file_exists($outputFilePath)) {
            $error = "La commande de correction n'a généré aucun fichier";
        }

        $result = [
            'duration' => $duration,
            'filesize' => filesize($outputFilePath),
        ];

        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }

    private function validerFichier($inputFilePath)
    {
        echo "  - Validation... ";

        $start = microtime(true);
        try {
            $this->fichierCinesValidator->isValid($inputFilePath);
        }
        catch (RuntimeException $e) {
            echo $e->getMessage();
        }
        $duration = round(microtime(true) - $start, 2);
        echo sprintf("(%s secondes)", $duration);
        echo PHP_EOL;

        /**
         * @var array $output
         *
         * > Exemple de résultat négatif :
         *
         * array(5) {
         *      [0]=>
         *      string(480) "<?xml version="1.0" encoding="UTF-8" standalone="yes"?><validator xmlns="http://facile.cines.fr"><fileName>2014-FOSSEZ-KEVIN.pdf</fileName><valid>false</valid><wellFormed>false</wellFormed><archivable>false</archivable><md5sum>942bbfc198b514005dae345e6ceacf3c</md5sum><sha256sum>274b716fcf7c0f558351067c516d6d0313c7488bf13db8287825cabaf82c9dea</sha256sum><size>4610333</size><format>PDF</format><version>1.4</version><encoding>NA</encoding><message>Invalid outline dictionary item"
         *      [1]=>
         *      string(37) "Annotation object is not a dictionary"
         *      [2]=>
         *      string(51) "Expected dictionary for font entry in page resource"
         *      [3]=>
         *      string(49) "Unexpected exception java.lang.ClassCastException"
         *      [4]=>
         *      string(22) "</message></validator>"
         * }
         *
         * > Exemple de résultat positif :
         *
         * array(1) {
         *      [0]=>
         *      string(474) "<?xml version="1.0" encoding="UTF-8" standalone="yes"?><validator xmlns="http://facile.cines.fr"><fileName>2014-FOSSEZ-KEVIN_cines.pdf</fileName><valid>true</valid><wellFormed>true</wellFormed><archivable>true</archivable><md5sum>9215f062a9937c3f5211b9f415b5b36d</md5sum><sha256sum>bfb980ee1a9e6a8c1109ea81b61c296079927fab6f7b6a3206c03f1e1cce378d</sha256sum><size>4597490</size><format>PDF</format><version>1.4</version><encoding>NA</encoding><message></message></validator>"
         * }
         */

        $result = $this->fichierCinesValidator->getArrayResult();

        if ($result[self::XML_TAG_SHA256SUM] && $result[self::XML_TAG_SHA256SUM] !== hash_file("sha256", $inputFilePath)) {
            throw new RuntimeException("Le checksum retourné par le web service ne correspond pas au checksum du fichier original");
        }

        $result['duration'] = $duration;

        return $result;
    }

    private function saveLigneResultat(array $row)
    {
        echo "  - Écriture résultat... : ";
        echo '"' . $this->csvOutputFilePath . '"' . PHP_EOL;
        fputcsv($this->getCsvOutputFileHandle(), $row, self::CSV_DELIMITER);
    }

    /**
     * @param string $name
     * @return \Application\Command\ShellCommandInterface
     */
    private function getRetraitementCommand($name)
    {
        switch ($name) {
            /*********************************** cines ****************************************/
            case self::COMMAND_CINES:
                $command = new RetraitementShellCommandCines();
                return $command;
                break;

            /*********************************** mines ****************************************/
            case self::COMMAND_MINES:
                $command = new RetraitementShellCommandMines();
                return $command;
                break;
        }

        throw new RuntimeException("Commande spécifiée inconnue : " . $name);
    }

    /**
     * @return mixed
     */
    public function getCsvInputFileHandle()
    {
        if (!$this->csvInputFileHandle) {
            $this->csvInputFileHandle = fopen($this->inputFilePath, "r");
            if ($this->csvInputFileHandle === false) {
                throw new RuntimeException("Impossible d'ouvrir en lecture le fichier " . $this->inputFilePath);
            }
        }

        return $this->csvInputFileHandle;
    }

    /**
     * @return mixed
     */
    public function getCsvOutputFileHandle()
    {
        if (!$this->csvOutputFileHandle) {
            $this->csvOutputFileHandle = fopen($this->csvOutputFilePath, "a");
            if ($this->csvOutputFileHandle === false) {
                throw new RuntimeException("Impossible d'ouvrir en écriture le fichier " . $this->csvOutputFilePath);
            }
        }

        return $this->csvOutputFileHandle;
    }
}
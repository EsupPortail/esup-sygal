<?php

namespace SygalApiImplTest;

use RuntimeException;
use stdClass;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Webmozart\Assert\Assert;

abstract class AbstractJsonDataProvider
{
    protected string $jsonTemplateDirPath;
    protected string $jsonTemplateFileName;

    protected string $jsonOutputDirPath = '/tmp';

    /**
     * @param string $jsonTemplateDirPath Chemin du répertoire contenant le·s template·s Twig.
     * @param string|null $jsonOutputDirPath Chemin du répertoire contenant le·s template·s Twig.
     */
    public function __construct(string $jsonTemplateDirPath, ?string $jsonOutputDirPath = null)
    {
        $this->setJsonTemplateDirPath($jsonTemplateDirPath);
        $this->setJsonOutputDirPath($jsonOutputDirPath ?: uniqid('/tmp/sygal_unit_tests_'));
    }

    /**
     * Spécifie le chemin du répertoire contenant le·s template·s Twig.
     */
    public function setJsonTemplateDirPath(string $jsonTemplateDirPath): void
    {
        $this->jsonTemplateDirPath = $jsonTemplateDirPath;
    }

    /**
     * Spécifie le chemin du répertoire dans lequel seront générés les fichiers .json.
     */
    public function setJsonOutputDirPath(string $jsonOutputDirPath): void
    {
        $this->jsonOutputDirPath = $jsonOutputDirPath;
        Assert::false(file_exists($this->jsonOutputDirPath), "Le répertoire destination $this->jsonOutputDirPath existe déjà");
        mkdir($this->jsonOutputDirPath);
    }

    /**
     * Spécifie le nom (pas le chemin) du fichier template Twig à utiliser.
     */
    public function setJsonTemplateFileName(string $jsonTemplateFileName): void
    {
        $this->jsonTemplateFileName = $jsonTemplateFileName;
    }

    /**
     * Génère les données JSON, en passant par un fichier .json temporaire généré par le moteur Twig.
     *
     * @param array $templateParams Paramètres transmis au moteur Twig
     * @param bool $keepTempFile Garder ou pas le fichier .json temporaire ?
     */
    public function generateJsonData(array $templateParams, bool $keepTempFile = false): stdClass
    {
        $jsonFileName = uniqid('data_');
        $jsonFilePath = $this->generateJsonFile($jsonFileName, $templateParams);
        $json = file_get_contents($jsonFilePath);
        if (!$keepTempFile) {
            unlink($jsonFilePath);
            fwrite(STDERR, "Fichier JSON supprimé : $jsonFilePath" . PHP_EOL);
        }

        return (object) json_decode($json, true);
    }

    /**
     * Génère un fichier .json.
     *
     * @param string $jsonFileName Nom du fichier à générer, sans l'extension
     * @param array $templateParams Paramètres transmis au moteur Twig
     * @return string Chemin du fichier généré
     */
    public function generateJsonFile(string $jsonFileName, array $templateParams): string
    {
        $jsonFilePath = $this->computeJsonOutputFilePath($jsonFileName);
        Assert::false(file_exists($jsonFilePath), "Le fichier de sortie '$jsonFilePath' existe déjà");

        $loader = new FilesystemLoader($this->jsonTemplateDirPath);
        $twig = new Environment($loader, ['strict_variables' => true]);
        try {
            $content = $twig->render($this->jsonTemplateFileName, $templateParams);
        } catch (Error $e) {
            throw new RuntimeException("Erreur Twig rencontrée", null, $e);
        }

        file_put_contents($jsonFilePath, $content);
        chmod($jsonFilePath, 0664);
        fwrite(STDERR, "Fichier JSON créé : $jsonFilePath" . PHP_EOL);

        return $jsonFilePath;
    }

    protected function computeJsonOutputFilePath(string $jsonFileName, ?string $ouputDirPath = null): string
    {
        return ($ouputDirPath ?: $this->jsonOutputDirPath) . '/' . $jsonFileName . '.json';
    }
}
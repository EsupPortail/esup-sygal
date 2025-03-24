<?php

namespace SygalApiImplTest;

use RuntimeException;
use stdClass;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Webmozart\Assert\Assert;

trait InscriptionAdministrativeJsonTrait
{
    static protected string $jsonOutputDirPath = '/tmp';
    static protected string $jsonTemplateFilesDir;

    protected string $jsonOriginalFileName = 'inscr_admin';
    protected string $jsonTemplateFileName = 'inscr_admin.json.twig';

    public static function setJsonTemplateFilesDir(string $jsonTemplateFilesDir): void
    {
        self::$jsonTemplateFilesDir = $jsonTemplateFilesDir;
    }

    public static function setJsonOutputDirPath(string $jsonOutputDirPath): void
    {
        self::$jsonOutputDirPath = $jsonOutputDirPath;
        mkdir(self::$jsonOutputDirPath);
    }

    protected function generateJsonData(array $context, bool $keepTempFile = false): stdClass
    {
        $jsonFileName = uniqid('sygal_json_', $this->jsonOriginalFileName);
        $jsonFilePath = $this->generateJsonFile($jsonFileName, $context);
        $json = file_get_contents($jsonFilePath);
        if (!$keepTempFile) {
            unlink($jsonFilePath);
            fwrite(STDERR, "Fichier JSON supprimé : $jsonFilePath" . PHP_EOL);
        }

        return (object) json_decode($json, true);
    }

    protected function generateJsonFile(string $jsonFileName, array $templateParams): string
    {
        $jsonFilePath = $this->computeJsonOutputFilePath($jsonFileName);

        $loader = new FilesystemLoader(self::$jsonTemplateFilesDir);
        $twig = new Environment($loader);
        try {
            $content = $twig->render($this->jsonTemplateFileName, $templateParams);
        } catch (Error $e) {
            throw new RuntimeException("Erreur Twig rencontrée", null, $e);
        }

        Assert::false(file_exists($jsonFilePath), "Le fichier de sortie '$jsonFilePath' existe déjà");

        file_put_contents($jsonFilePath, $content);
        fwrite(STDERR, "Fichier JSON créé : $jsonFilePath" . PHP_EOL);

        return $jsonFilePath;
    }

    protected function computeJsonOutputFilePath(string $jsonFileName, ?string $ouputDirPath = null): string
    {
        return ($ouputDirPath ?: static::$jsonOutputDirPath) . '/' . $jsonFileName . '.json';
    }
}
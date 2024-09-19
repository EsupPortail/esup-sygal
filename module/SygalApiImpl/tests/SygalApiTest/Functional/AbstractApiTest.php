<?php

namespace SygalApiTest\Functional;

use ApplicationUnitTest\Controller\AbstractControllerTestCase;
use RuntimeException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Webmozart\Assert\Assert;

abstract class AbstractApiTest extends AbstractControllerTestCase
{
    protected string $applicationConfigPath = __DIR__ . '/../../../../../../config/application.config.php';
    protected $traceError = true;

    static protected int $testSourceId;
    static protected string|null $testSourceCode = null;

    protected string $apiUrl;
    protected string $curlHttpReturnCodePregPattern = '#^(.*);return=(\d+)$#';

    protected string $jsonTemplateFilesDir;
    protected string $jsonTemplateFileName;

    static protected string $jsonOutputDirPath = '/tmp';

    protected string $cmd;
    protected array $cmdOutput;
    protected int $cmdReturnCode;

    protected string $curlJsonResponse;
    protected string $curlHttpReturnCode;

    protected function generateJson(string $jsonFileNameNew, array $templateParams): void
    {
        $jsonFilePathNew = $this->computeJsonOutputFilePath($jsonFileNameNew);

        $loader = new FilesystemLoader($this->jsonTemplateFilesDir);
        $twig = new Environment($loader);
        try {
            $xslFileContent = $twig->render($this->jsonTemplateFileName, $templateParams);
        } catch (Error $e) {
            throw new RuntimeException("Erreur Twig rencontrée", null, $e);
        }

        Assert::false(file_exists($jsonFilePathNew), "Le fichier de sortie '$jsonFilePathNew' existe déjà");

        file_put_contents($jsonFilePathNew, $xslFileContent);
    }

    /**
     * @deprecated
     */
    protected function replaceStringInFile(string $from, string $to, string $jsonFileName, string $jsonFileNameNew): void
    {
        $jsonFilePath = $this->computeJsonOutputFilePath($jsonFileName);
        $jsonFilePathNew = $this->computeJsonOutputFilePath($jsonFileNameNew);

        Assert::true(file_exists($jsonFilePath), "Le fichier d'entrée %s est introuvable");
        Assert::false(file_exists($jsonFilePathNew), "Le fichier de sortie %s existe déjà");

        file_put_contents($jsonFilePathNew, str_replace($from, $to, file_get_contents($jsonFilePath)));
    }

    protected function computeJsonOutputFilePath(string $jsonFileName, ?string $ouputDirPath = null): string
    {
        return ($ouputDirPath ?: static::$jsonOutputDirPath) . '/' . $jsonFileName . '.json';
    }

    /**
     * @param string $jsonFileName
     */
    protected function callApi(string $jsonFileName): void
    {
        $jsonFilePath = $this->computeJsonOutputFilePath($jsonFileName);
        $this->cmd = $this->generateCurlCommand($jsonFilePath);

        //echo "Envoi du fichier '$jsonFileName'..." . PHP_EOL;
        exec($this->cmd, $output, $returnCode);
        $this->cmdOutput = $output;
        $this->cmdReturnCode = $returnCode;

//        var_dump($this->cmd,$output, $returnCode);
        if ($this->cmdReturnCode !== 0) {
            $this->markTestSkipped("Erreur lors du lancement de la ligne de commande '$this->cmd' : " . implode(PHP_EOL, $this->cmdOutput));
        }

        if (preg_match($this->curlHttpReturnCodePregPattern, implode('', $this->cmdOutput), $matches) !== false) {
            $this->curlJsonResponse = $matches[1];
            $this->curlHttpReturnCode = $matches[2];
//            if (intval($this->curlHttpReturnCode) >= '300') {
//                $this->markTestSkipped(
//                    "Le code de retour HTTP de la commande curl est imprévu ! " . PHP_EOL . PHP_EOL .
//                    "Commande curl : " . $this->cmd . PHP_EOL . PHP_EOL .
//                    "Code de retour HTTP : " . $this->curlHttpReturnCode . PHP_EOL . PHP_EOL .
//                    "Réponse : " . json_encode(json_decode($this->curlJsonResponse), JSON_PRETTY_PRINT) . PHP_EOL
//                );
//            }
        } else {
            $this->curlJsonResponse = null;
            $this->curlHttpReturnCode = null;
        }
    }

    protected function generateCurlCommand(string $jsonFilePath): string
    {
        $url = $this->apiUrl;

        return <<<EOT
curl \
--location \
--write-out ";return=%{http_code}\\n" \
--no-progress-meter --insecure \
-H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Basic cGVnYXNlOmF6ZXJ0eQ==" \
-X POST --data-binary "@$jsonFilePath" \
-b XDEBUG_SESSION=PHPSTORM \
$url
EOT;
    }
}
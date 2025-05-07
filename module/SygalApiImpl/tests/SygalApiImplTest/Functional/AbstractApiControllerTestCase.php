<?php

namespace SygalApiImplTest\Functional;

use ApplicationUnitTest\Controller\AbstractControllerTestCase;

abstract class AbstractApiControllerTestCase extends AbstractControllerTestCase
{
    protected string $applicationConfigPath = __DIR__ . '/../../../../../../config/application.config.php';
    protected $traceError = true;

    static protected int $testSourceId;
    static protected string|null $testSourceCode = null;

    protected string $apiUrl;
    protected string $curlHttpReturnCodePregPattern = '#^(.*);return=(\d+)$#';

    protected string $cmd;
    protected array $cmdOutput;
    protected int $cmdReturnCode;

    protected string $curlJsonResponse;
    protected string $curlHttpReturnCode;

    /**
     * @param string $jsonFilePath Chemin vers le fichier JSON à envoyer
     */
    protected function callApi(string $jsonFilePath): void
    {
        $this->cmd = $this->generateCurlCommand($jsonFilePath);

        //echo "Envoi du fichier '$jsonFileName'..." . PHP_EOL;
        exec($this->cmd, $output, $returnCode);
        $this->cmdOutput = $output;
        $this->cmdReturnCode = $returnCode;
        fwrite(STDERR, "Commande : $this->cmd" . PHP_EOL);
        fwrite(STDERR, "Output : " . implode(PHP_EOL, $output) . PHP_EOL);
        fwrite(STDERR, "Return code : $returnCode" . PHP_EOL);

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
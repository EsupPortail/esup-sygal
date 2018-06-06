<?php

namespace ApplicationFunctionalTest\Command;

use Application\Command\Exception\CommandExecutionException;
use Application\Command\ValidationFichierCinesCommand;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\View\Model\CsvModel;
use UnicaenApp\View\Renderer\CsvRenderer;

class ValidationFichierCinesCommandTest extends \PHPUnit_Framework_TestCase
{
    private $rootDir = __DIR__ . '/../../../../..';

    public function getSampleFilesPaths()
    {
        return [
            [
                $filePath = $this->realSampleFilePath(),
                $logFilePath = '/tmp/test_ws_multiple_with_sample.csv',
                $sleepDuration = 0, // 1 min
            ],
//            [
//                '/home/gauthierb/Bureau/these-archivable.pdf',
//                '/tmp/test_ws_multiple_with_these-archivable.csv',
//                60,
//            ],
//            [
//                '/home/gauthierb/Bureau/these-non-archivable.pdf',
//                '/tmp/test_ws_multiple_with_these-non-archivable.csv',
//                60,
//            ],
        ];
    }

    /**
     * @dataProvider getSampleFilesPaths
     * @param string $filePath
     * @param string $logFilePath
     * @param int    $sleepDuration
     */
    public function test_calling_ws_multiple_times_with_same_file_returns_same_result($filePath, $logFilePath, $sleepDuration)
    {
        $noCalls = 50;

        $csvFile = $logFilePath;
        $scriptPath = $this->realScriptPath();
        $command = new ValidationFichierCinesCommand($scriptPath);

        for ($i = 1; $i <= $noCalls; $i++) {
            $command->execute($filePath);
            $result = $command->getResult();
            $arrayResult = $command->getArrayResult();
            $arrayResult = array_map(function($value) { return is_bool($value) ? ['N','O'][$value] : $value; }, $arrayResult);

            $sameHash = $arrayResult['sha256sum'] === hash('sha256', file_get_contents($filePath));

            $arrayResult = array_merge(
                ['date' => date('d/m/Y H:i:s'), 'file' => $filePath],
                $arrayResult,
                ['log' => $result, 'sameSha256' => $sameHash ? 'O' : 'N']
            );
            $this->writeResultToFile($arrayResult, $csvFile);

            if ($sleepDuration > 0) {
                sleep($sleepDuration);
            }
        }
    }

    public function test_calling_ws_using_maxtime_throws_exception_when_maxtime_is_reached()
    {
        $scriptPath = $this->realScriptPath();
        $sampleFilePath = $this->realSampleFilePath();
        $maxExecTime = 5; // sec

        $command = new ValidationFichierCinesCommand($scriptPath);

        try {
            $command->execute($sampleFilePath, 'http://example.com:81', $maxExecTime);
        } catch (RuntimeException $e) {
            $previous = $e->getPrevious();
            $this->assertInstanceOf(CommandExecutionException::class, $previous);
            $this->assertContains("délai maximum", $previous->getMessage());
            return;
        }

        $this->fail('Une exception de type ' . RuntimeException::class . ' aurait dû être levée');
    }

    private function writeResultToFile(array $result, $filePath)
    {
        $this->writeResultsToFile([$result], $filePath);
    }
    private function writeResultsToFile(array $results, $filePath)
    {
        $header = file_exists($filePath) ? [] : array_keys(current($results));

        $csvModel = new CsvModel();
        $csvModel->setHeader($header);
        $csvModel->setData($results);
        $csvRenderer = new CsvRenderer();
        $fileContent = $csvRenderer->render($csvModel);

        file_put_contents($filePath, $fileContent, FILE_APPEND);
    }

    private function realScriptPath()
    {
        return realpath($this->rootDir . '/bin/validation_cines.sh');
    }

    private function realSampleFilePath()
    {
        return realpath($this->rootDir . '/bin/from_cines/sample.pdf');
    }
}
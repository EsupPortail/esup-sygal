<?php

namespace ApplicationFunctionalTest\Command;

use Application\Command\CheckWSValidationFichierCinesCommand;
use Application\Command\ShellCommandRunner;

class CheckWSValidationFichierCinesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function test_throws_exception_if_sample_file_not_found()
    {
        $scriptPath = $this->createFakeScriptFile();

        $scriptRunner = new ShellCommandRunner();
        $scriptRunner->setScriptFilePath($scriptPath);
        $command = new CheckWSValidationFichierCinesCommand($scriptRunner);
        try {
            $command->execute();
        } catch (\InvalidArgumentException $e) {
            unlink($scriptPath);
            return;
        }
        unlink($scriptPath);
        $this->fail("Une exception devrait être levée lorsque le fichier sample.pdf n'existe pas");
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /La réponse du script de test du web service de validation indique qu'il a rencontré l'erreur '\/bin\/nc does does not exist'/
     */
    public function test_throws_exception_when_response_contains_error()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandRunner $scriptRunner */
        $scriptRunner = $this->createShellCommandRunnerMock();
        $scriptRunner
            ->method('run')
            ->willReturn("RESPONSE: UNKNOWN - ERROR: /bin/nc does does not exist|Response=ms;1000;5000;0");
        $command = new CheckWSValidationFichierCinesCommand($scriptRunner);
        $command->execute();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /La réponse du script de test du web service de validation n'est pas au format/
     */
    public function test_throws_exception_when_response_format_is_not_correct()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandRunner $scriptRunner */
        $scriptRunner = $this->createShellCommandRunnerMock();
        $scriptRunner
            ->method('run')
            ->willReturn("RAIPONCE: OK - 893 ms|Response=893ms;1000;5000;0");
        $command = new CheckWSValidationFichierCinesCommand($scriptRunner);
        $command->execute();
    }

    public function test_can_extract_values_from_response()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandRunner $scriptRunner */
        $scriptRunner = $this->createShellCommandRunnerMock();
        $scriptRunner
            ->method('run')
            ->willReturn($response = "RESPONSE: OK - 893 ms|Response=893ms;1000;5000;0");
        $command = new CheckWSValidationFichierCinesCommand($scriptRunner);
        $command->execute();
        $this->assertEquals($response, $command->getResult());
        $this->assertTrue($command->getBooleanStatusResult());
        $this->assertEquals(CheckWSValidationFichierCinesCommand::STATUS_OK, $command->getStatusResult());
        $this->assertEquals(893, $command->getDurationResult());
    }

    public function test_real_sample_pdf_file_exists()
    {
        $scriptRunner = new ShellCommandRunner();
        $scriptRunner->setScriptFilePath($this->realScriptPath());
        $this->assertFileIsReadable($scriptRunner->getScriptDirPath() . '/sample.pdf');
    }

    public function test_real_web_service_boolean_status_response()
    {
        $command = new CheckWSValidationFichierCinesCommand($this->realScriptPath());
        $command->execute();

        $worstAcceptableStatus = CheckWSValidationFichierCinesCommand::STATUS_WARNING;

        $this->assertTrue(
            $command->getBooleanStatusResult($worstAcceptableStatus),
            sprintf("le statut devrait être '%s' au pire, or voici le statut reçu: %s (%s ms)",
                $worstAcceptableStatus, $command->getStatusResult(), $command->getDurationResult()));
    }

    private function createShellCommandRunnerMock()
    {
        $scriptPath = $this->createFakeScriptFile();
        $this->createFakeSampleFile();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ShellCommandRunner $scriptRunner */
        $scriptRunner = $this->getMockBuilder(ShellCommandRunner::class)
            ->setConstructorArgs([$scriptPath])
            ->setMethods(['run'])
            ->getMock();

        return $scriptRunner;
    }

    private function realScriptPath()
    {
        return __DIR__ . '/../../../../../bin/from_cines/check_webservice_response.sh';
    }

    private function createFakeScriptFile()
    {
        $filePath = sys_get_temp_dir() . sprintf('/fake_script.sh');
        touch($filePath);

        return $filePath;
    }

    private function createFakeSampleFile()
    {
        $filePath = sys_get_temp_dir() . '/sample.pdf';
        touch($filePath);

        return $filePath;
    }

    protected function tearDown()
    {
        if (file_exists($path = sys_get_temp_dir() . '/fake_script.sh')) {
            unlink($path);
        }
        if (file_exists($path = sys_get_temp_dir() . '/sample.pdf')) {
            unlink($path);
        }
    }
}
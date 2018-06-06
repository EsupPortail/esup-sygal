<?php

namespace ApplicationFunctionalTest\Command;

use Application\Command\ShellScriptRunner;
use UnicaenApp\Exception\LogicException;

class ShellScriptRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_invalid_script_path_causes_exception_at_execution()
    {
        $invalidScriptPath = __DIR__ . '/invalid_script_path.sh';
        $scriptRunner = new ShellScriptRunner($invalidScriptPath);
        $scriptRunner->run();
    }

    /**
     * @expectedException LogicException
     */
    public function test_asking_for_return_code_before_running_throws_exception()
    {
        $scriptRunner = new ShellScriptRunner('peu importe le script path');
        $scriptRunner->getReturnCode();
    }

    public function test_async_mode_causes_return_code_to_be_always_0()
    {
        $scriptPath = $this->createSimpleScript('exit 1');

        $scriptRunner = new ShellScriptRunner($scriptPath);
        $scriptRunner->setAsync();
        $scriptRunner->run();

        $this->assertEquals(0, $scriptRunner->getReturnCode());
    }

    /**
     * @expectedException LogicException
     */
    public function test_setting_async_mode_resets_running_state_to_false()
    {
        $scriptPath = $this->createSimpleScript();

        $scriptRunner = new ShellScriptRunner($scriptPath);
        $scriptRunner->run();
        $scriptRunner->getReturnCode();
        $scriptRunner->setAsync(); // should resets 'has run' flag to false
        $scriptRunner->getReturnCode();
    }

    public function test_async_mode_uses_nohup()
    {
        $scriptPath = $this->createSimpleScript();

        $scriptRunner = new ShellScriptRunner($scriptPath);
        $scriptRunner->setAsync();

        $this->assertStringStartsWith('nohup ', $scriptRunner->getAsyncCommandToString());
    }

    public function test_running_method_returns_string()
    {
        $scriptPath = $this->createSimpleScript();

        $scriptRunner = new ShellScriptRunner($scriptPath);

        $this->assertInternalType('string', $scriptRunner->run());
    }

    public function test_async_mode_causes_running_method_to_return_null()
    {
        $scriptPath = $this->createSimpleScript();

        $scriptRunner = new ShellScriptRunner($scriptPath);
        $scriptRunner->setAsync();

        $this->assertNull($scriptRunner->run());
    }

    /**
     * @param string $additionnalContent
     * @return string
     */
    private function createSimpleScript($additionnalContent = null)
    {
        $scriptPath = sys_get_temp_dir() . '/script_for_shell_runner_test.sh';
        touch($scriptPath);
        chmod($scriptPath, 0700);

        $content = <<<BASH
#!/usr/bin/env bash
echo "Hello world!"
$additionnalContent
BASH;
        file_put_contents($scriptPath, $content);

        return $scriptPath;
    }
}

<?php

namespace ApplicationFunctionalTest\Command;

use Application\Command\ShellScriptRunner;

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

}

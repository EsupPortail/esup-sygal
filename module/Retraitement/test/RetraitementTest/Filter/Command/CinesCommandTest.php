<?php

namespace RetraitementTest\Filter\Command;

use Application\Command\Exception\ShellCommandException;
use Retraitement\Filter\Command\RetraitementShellCommandCines;

class CinesCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckingResourcesDoesNotFail()
    {
        $exception = null;

        $command = new RetraitementShellCommandCines();
        try {
            $command->checkRequirements();
        }
        catch (ShellCommandException $e) {
            $exception = $e;
        }

        $this->assertNull($exception, sprintf(
            "La vérification des ressources par la commande '%s' n'aurait pas dû lever une ShellCommandException.",
            $command->getName()
        ));
    }
}
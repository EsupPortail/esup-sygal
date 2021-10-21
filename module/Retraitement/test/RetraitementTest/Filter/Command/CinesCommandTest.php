<?php

namespace RetraitementTest\Filter\Command;

use Application\Command\Exception\ShellCommandException;
use Retraitement\Filter\Command\RetraitementShellCommandCines;
use UnicaenApp\Exception\RuntimeException;

class CinesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckingResourcesDoesNotFail()
    {
        $command = new RetraitementShellCommandCines();
        try {
            $command->checkRequirements();
        }
        catch (ShellCommandException $e) {
            $this->fail(sprintf(
                "La vérification des ressources par la commande '%s' n'aurait pas dû lever une %s.",
                $command->getName(),
                get_class($e)
            ));
        }
    }
}
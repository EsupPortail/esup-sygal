<?php

namespace RetraitementTest\Filter\Command;

use Retraitement\Filter\Command\CinesCommand;
use UnicaenApp\Exception\RuntimeException;

class CinesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckingResourcesDoesNotFail()
    {
        $command = new CinesCommand();
        try {
            $command->checkResources();
        }
        catch (RuntimeException $e) {
            $this->fail(sprintf(
                "La vérification des ressources par la commande '%s' n'aurait pas dû lever une %s.",
                $command->getName(),
                get_class($e)
            ));
        }
    }
}
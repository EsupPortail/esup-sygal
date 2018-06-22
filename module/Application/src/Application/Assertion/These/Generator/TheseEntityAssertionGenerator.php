<?php

namespace Application\Assertion\These\Generator;

use Application\Assertion\These\TheseEntityAssertion;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Zend\Code\Generator\DocBlockGenerator;

class TheseEntityAssertionGenerator extends AssertionGenerator
{
    protected function addUses()
    {
        $this->assertionClassGenerator->addUse(ThesePrivileges::class);
        $this->assertionClassGenerator->addUse(ValidationPrivileges::class);
    }

    protected function addDockBlock()
    {
        $desc = sprintf('Générée le %s par %s à partir du fichier %s.',
            date('d/m/Y H:i:s'),
            get_class() . PHP_EOL,
            $this->ruleFilePath
        );

        $this->assertionClassGenerator->setDocBlock(DocBlockGenerator::fromArray([
            'shortDescription' => sprintf('Classe mère pour %s.', TheseEntityAssertion::class),
            'longDescription'  => $desc,
        ]));
    }
}
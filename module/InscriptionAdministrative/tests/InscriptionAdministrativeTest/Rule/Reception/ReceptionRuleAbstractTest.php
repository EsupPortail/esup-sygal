<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class ReceptionRuleAbstractTest extends TestCase
{
    protected InscriptionAdministrative|MockObject $ia;
    protected InscriptionAdministrativeApprenant|MockObject $iaApprenant;

    protected function setUp(): void
    {
        $this->ia = $this->createMock(InscriptionAdministrative::class);
    }

    abstract protected function createRule(): ReceptionRuleInterface;
}
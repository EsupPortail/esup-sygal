<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use InscriptionAdministrative\Rule\Reception\AbstractReceptionRule;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use InvalidArgumentException;

class AbstractReceptionRuleTest extends ReceptionRuleAbstractTest
{
    public function test_inscription_sans_apprenant_throws_exception(): void
    {
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeApprenant')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }

    protected function createRule(): ReceptionRuleInterface
    {
        $rule = $this->getMockForAbstractClass(AbstractReceptionRule::class);
        $rule->setDoctorantUpdateEnabled();

        return $rule;
    }
}

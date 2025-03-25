<?php

namespace InscriptionAdministrativeTest\Rule\Reception\StatutAnnulee;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRule;
use InscriptionAdministrativeTest\Rule\Reception\ReceptionRuleAbstract;
use InvalidArgumentException;

class StatutAnnuleeRuleTest extends ReceptionRuleAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iaApprenant = $this->createMock(InscriptionAdministrativeApprenant::class);

        $this->ia->method('estAnnulee')->willReturn(true);
    }

    public function test_inscription_sans_apprenant_throws_exception(): void
    {
        $rule = $this->createRule();

        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn(null);
        $rule->setInscriptionAdministrative($this->ia);

        $this->expectException(InvalidArgumentException::class);
        $rule->execute();
    }

    public function test_throws_exception_with_inscription_non_annulee(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $this->expectException(InvalidArgumentException::class);

        $ia = $this->createMock(InscriptionAdministrative::class);
        $ia->expects(static::once())->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);
        $ia->expects(static::once())->method('estAnnulee')->willReturn(false);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($ia);
        $rule->execute();
    }

    public function test_inscription_annulee(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canPerformAnyAction(),
            "La règle ne devrait pas répondre qu'on peut réaliser la moindre action"
        );
    }

    protected function createRule(): StatutAnnuleeRule
    {
        $rule = new StatutAnnuleeRule();

        return $rule;
    }
}

<?php

namespace InscriptionAdministrativeTest\Rule\Reception\StatutAutre;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use InscriptionAdministrative\Rule\Reception\StatutAutre\StatutAutreRule;
use InscriptionAdministrativeTest\Rule\Reception\ReceptionRuleAbstract;
use InvalidArgumentException;

class StatutAutreRuleTest extends ReceptionRuleAbstract
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iaApprenant = $this->createMock(InscriptionAdministrativeApprenant::class);
    }

    public function test_inscription_sans_apprenant_throws_exception(): void
    {
        $rule = $this->createRule();

        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn(null);
        $rule->setInscriptionAdministrative($this->ia);

        $this->expectException(InvalidArgumentException::class);
        $rule->execute();
    }

    public function test_throws_exception_if_statut_validee(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $this->ia->expects(static::once())->method('estValidee')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }

    public function test_throws_exception_if_statut_annulee(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $this->ia->expects(static::once())->method('estAnnulee')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }

    public function test_cannot_perform_any_action(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $this->ia->expects(static::once())->method('estAnnulee')->willReturn(false);
        $this->ia->expects(static::once())->method('estValidee')->willReturn(false);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canPerformAnyAction(),
            "La règle ne devrait pas répondre qu'on peut réaliser une action"
        );
    }

    protected function createRule(): ReceptionRuleInterface
    {
        $rule = new StatutAutreRule();

        return $rule;
    }
}

<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InvalidArgumentException;

class InscriptionAutreReceptionRuleTest extends AbstractReceptionRuleTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testInscriptionNiValideeNiAnnuleeThrowsException(): void
    {
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeApprenant')->willReturn(
            $this->createMock(InscriptionAdministrativeApprenant::class)
        );

        $this->ia->expects($this->once())->method('estValidee')->willReturn(false);
        $this->ia->expects($this->once())->method('estAnnulee')->willReturn(false);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }

    public function testInscriptionSansApprenantThrowsException(): void
    {
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeApprenant')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }
}

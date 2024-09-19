<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InvalidArgumentException;

class InscriptionAnnuleeReceptionRuleTest extends AbstractReceptionRuleTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ia->expects($this->once())->method('getInscriptionAdministrativeApprenant')->willReturn(
            $this->createMock(InscriptionAdministrativeApprenant::class)
        );
    }

    public function testInscriptionAnnulee(): void
    {
        // IA annulée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(false);
        $this->ia->expects($this->once())->method('estAnnulee')->willReturn(true);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        $this->assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        $this->assertFalse(
            $rule->canUpdateCodeApprenantDoctorantFromApprenant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        $this->assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        $this->assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
    }
}

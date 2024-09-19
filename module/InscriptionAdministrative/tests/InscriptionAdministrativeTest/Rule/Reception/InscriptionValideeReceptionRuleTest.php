<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etat;
use Doctorant\Entity\Db\Doctorant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Rule\Reception\ReceptionRule;
use InvalidArgumentException;
use These\Entity\Db\These;
use UnicaenApp\Traits\MessageAwareInterface;

class InscriptionValideeReceptionRuleTest extends AbstractReceptionRuleTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ia->expects($this->once())->method('getInscriptionAdministrativeApprenant')->willReturn(
            $this->createMock(InscriptionAdministrativeApprenant::class)
        );
    }

    public function testDoctorantDejaLieEtAyantUneThese(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA déjà liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(
            $this->createMock(InscriptionAdministrativeDoctorant::class)
        );

        // These Existante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$this->createMock(These::class)]);

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

        $this->assertNotNull($rule->getDoctorant());
    }

    public function testDoctorantDejaLieEtAdmissionValidee(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA déjà liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(
            $this->createMock(InscriptionAdministrativeDoctorant::class)
        );

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        $this->assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
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

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantDejaLieEtAdmissionNonValidee(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA déjà liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(
            $this->createMock(InscriptionAdministrativeDoctorant::class)
        );

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission non validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn('AUTRE CHOSE QUE VALIDE');
        $admission->method('getEtat')->willReturn($etatAdmission);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertContains(
            $msg = ReceptionRule::ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
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

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantDejaLieEtSansAdmission(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA déjà liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(
            $this->createMock(InscriptionAdministrativeDoctorant::class)
        );

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission inexistante
        $this->admissionRepository->method('findOneByDoctorant')->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertContains(
            $msg = ReceptionRule::ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
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

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNull($rule->getAdmission());
    }

    public function testDoctorantNonLieAyantUneThese(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($this->createMock(Doctorant::class));

        // These Existante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$this->createMock(These::class)]);

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
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
    }

    public function testDoctorantNonLieAvecAdmissionValidee(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($this->createMock(Doctorant::class));

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        $this->assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
        );
        $this->assertFalse(
            $rule->canUpdateCodeApprenantDoctorantFromApprenant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        $this->assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantNonLieeEtAdmissionNonValidee(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($this->createMock(Doctorant::class));

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission non validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn('AUTRE CHOSE QUE VALIDE');
        $admission->method('getEtat')->willReturn($etatAdmission);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertContains(
            $msg = ReceptionRule::ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
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
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantNonLieSansAdmission(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($this->createMock(Doctorant::class));

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission inexistante
        $this->admissionRepository->method('findOneByDoctorant')->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertContains(
            $msg = ReceptionRule::ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
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
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNull($rule->getAdmission());
    }

    public function testDoctorantNonLieEtAdmissionValideeAvecDoctorantLie(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission existante avec numero candidat
        $admission = $this->createStub(Admission::class);
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn($admission);

        // admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        // admission déjà liée à un doctorant
        $doctorant = $this->createStub(Doctorant::class);
        $admission->method('getDoctorant')->willReturn($doctorant);

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
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantNonLieEtAdmissionValideeSansDoctorantLie(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission existante avec numero candidat
        $admission = $this->createStub(Admission::class);
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn($admission);

        // admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        // admission non liée à un doctorant
        $admission->method('getDoctorant')->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertTrue(
            $rule->canCreateDoctorant(),
            "La règle devrait répondre qu'on peut créer le doctorant"
        );
        $this->assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
        );
        $this->assertFalse(
            $rule->canUpdateCodeApprenantDoctorantFromApprenant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        $this->assertTrue(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle devrait répondre qu'on peut lier l'admission au doctorant"
        );
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNull($rule->getDoctorant());
        $this->assertNotNull($rule->getAdmission());
    }

    public function testDoctorantNonLieAvecAdmissionNonValideeParNumeroCandidat(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission existante avec numero candidat
        $admission = $this->createStub(Admission::class);
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn($admission);

        // admission non validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn('AUTRE CHOSE QUE VALIDE');
        $admission->method('getEtat')->willReturn($etatAdmission);

        $this->expectException(InvalidArgumentException::class);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();
    }

    public function testDoctorantNonLieMaisTrouveAvecIneSansAdmission(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission inexistante avec numero candidat
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn(null);

        // doctorant trouvé avec cet INE
        $doctorant = $this->createMock(Doctorant::class);
        $this->doctorantRepository->method('findOneByIne')->willReturn($doctorant);

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
        $this->assertTrue(
            $rule->canUpdateCodeApprenantDoctorantFromApprenant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        $this->assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNotNull($rule->getDoctorant());
        $this->assertNull($rule->getAdmission());
    }

    public function testDoctorantNonLieEtIntrouvableEtSansAdmission(): void
    {
        // IA validée
        $this->ia->expects($this->once())->method('estValidee')->willReturn(true);
        // IA non encore liée à un doctorant
        $this->ia->expects($this->once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission inexistante avec numero candidat
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn(null);

        // doctorant non trouvé avec cet INE
        $this->doctorantRepository->method('findOneByIne')->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        $this->assertTrue(
            $rule->canCreateDoctorant(),
            "La règle devrait répondre qu'on peut créer le doctorant"
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
        $this->assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );

        $this->assertNull($rule->getDoctorant());
        $this->assertNull($rule->getAdmission());
    }
}

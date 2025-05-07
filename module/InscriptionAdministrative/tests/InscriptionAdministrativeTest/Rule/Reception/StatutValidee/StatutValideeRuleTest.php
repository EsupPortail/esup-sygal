<?php

namespace InscriptionAdministrativeTest\Rule\Reception\StatutValidee;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etat;
use Admission\Entity\Db\Repository\AdmissionRepository;
use Admission\Service\Admission\AdmissionService;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\Repository\DoctorantRepository;
use Doctorant\Service\DoctorantService;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\Repository\IndividuRepository;
use Individu\Service\IndividuService;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRule;
use InscriptionAdministrativeTest\Rule\Reception\ReceptionRuleAbstract;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use These\Entity\Db\Repository\TheseAnneeUnivRepository;
use These\Entity\Db\Repository\TheseRepository;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use UnicaenApp\Traits\MessageAwareInterface;

class StatutValideeRuleTest extends ReceptionRuleAbstract
{
    protected InscriptionAdministrativeDoctorant|Stub $iaDoctorant;

    protected IndividuService|MockObject $individuService;
    protected DoctorantService|MockObject $doctorantService;
    protected TheseService|MockObject $theseService;
    protected TheseAnneeUnivService|MockObject $theseAnneeUnivService;
    protected AdmissionService|MockObject $admissionService;

    protected IndividuRepository|MockObject $individuRepository;
    protected DoctorantRepository|MockObject $doctorantRepository;
    protected TheseRepository|MockObject $theseRepository;
    protected TheseAnneeUnivRepository|MockObject $theseAnneeUnivRepository;
    protected AdmissionRepository|MockObject $admissionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->individuRepository = $this->createMock(IndividuRepository::class);
        $this->individuService = $this->createMock(IndividuService::class);
        $this->individuService->method('getRepository')->willReturn($this->individuRepository);

        $this->doctorantRepository = $this->createMock(DoctorantRepository::class);
        $this->doctorantService = $this->createMock(DoctorantService::class);
        $this->doctorantService->method('getRepository')->willReturn($this->doctorantRepository);

        $this->theseRepository = $this->createMock(TheseRepository::class);
        $this->theseService = $this->createMock(TheseService::class);
        $this->theseService->method('getRepository')->willReturn($this->theseRepository);

        $this->theseAnneeUnivRepository = $this->createMock(TheseAnneeUnivRepository::class);
        $this->theseAnneeUnivService = $this->createMock(TheseAnneeUnivService::class);
        $this->theseAnneeUnivService->method('getRepository')->willReturn($this->theseAnneeUnivRepository);

        $this->admissionRepository = $this->createMock(AdmissionRepository::class);
        $this->admissionService = $this->createMock(AdmissionService::class);
        $this->admissionService->method('getRepository')->willReturn($this->admissionRepository);

        $this->iaApprenant = $this->createMock(InscriptionAdministrativeApprenant::class);
        $this->iaDoctorant = $this->createMock(InscriptionAdministrativeDoctorant::class);

        $this->ia->method('estValidee')->willReturn(true);
    }

    public function test_inscription_sans_apprenant_throws_exception(): void
    {
        $rule = $this->createRule();

        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn(null);
        $rule->setInscriptionAdministrative($this->ia);

        $this->expectException(InvalidArgumentException::class);
        $rule->execute();
    }

    public function test_throws_exception_with_inscription_non_validee(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        $this->expectException(InvalidArgumentException::class);

        $ia = $this->createMock(InscriptionAdministrative::class);
        $ia->expects(static::once())->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);
        $ia->expects(static::once())->method('estValidee')->willReturn(false);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($ia);
        $rule->execute();
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_ayant_une_these_avec_annee_univ_existante(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // These Existante Pour Doctorant Lie
        $these = $this->createStub(These::class);
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$these]);

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);
        $tau = $this->createStub(TheseAnneeUniv::class)/*->method('getAnneeUniv')->willReturn(2024)*/;
        $this->theseAnneeUnivRepository->expects(static::once())->method('findOneTheseAnneeUnivForTheseAndAnnee')->with($these, 2024)->willReturn($tau);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'année universitaire pour la thèse"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertTrue(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle devrait répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertSame($rule->getFoundThese(), $these);
        static::assertSame($rule->getFoundTheseAnneeUniv(), $tau);
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_ayant_une_these_sans_annee_univ(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // These Existante Pour Doctorant Lie
        $these = $this->createStub(These::class);
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$these]);

        // aucune Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);
        $this->theseAnneeUnivRepository->expects(static::once())->method('findOneTheseAnneeUnivForTheseAndAnnee')->with($these, 2024)->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertTrue(
            $rule->canCreateTheseAnneeUniv(),
            "La règle devrait répondre qu'on peut créer l'année universitaire pour la thèse"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertSame($rule->getFoundThese(), $these);
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_ayant_plusieurs_theses(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // These Existante Pour Doctorant Lie
        $these1 = $this->createStub(These::class);
        $these2 = $this->createStub(These::class);
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$these1, $these2]);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'année universitaire pour la thèse"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_admission_validee(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
        );
        static::assertTrue(
            $rule->canCreateTheseAnneeUniv(),
            "La règle devrait répondre qu'on peut créer l'année universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_admission_non_validee(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission non validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn('AUTRE CHOSE QUE VALIDE');
        $admission->method('getEtat')->willReturn($etatAdmission);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertContains(
            $msg = StatutValideeRule::ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_deja_lie_et_sans_admission(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA déjà liée à un doctorant
        $individu = $this->createStub(Individu::class);
        $doctorant = $this->createStub(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->iaDoctorant->method('getDoctorant')->willReturn($doctorant);
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn($this->iaDoctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission inexistante
        $this->admissionRepository->method('findOneByDoctorant')->willReturn(null);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertContains(
            $msg = StatutValideeRule::ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertFalse(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_lie_ayant_une_these(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $doctorant = $this->createStub(Doctorant::class);
        $individu = $this->createStub(Individu::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($doctorant);

        // These Existante Pour Doctorant Lie
        $these = $this->createMock(These::class);
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([$these]);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);
        $tau = $this->createStub(TheseAnneeUniv::class)/*->method('getAnneeUniv')->willReturn(2024)*/;
        $this->theseAnneeUnivRepository->expects(static::once())->method('findOneTheseAnneeUnivForTheseAndAnnee')->with($these, 2024)->willReturn($tau);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertTrue(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle devrait répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertSame($rule->getFoundThese(), $these);
        static::assertSame($rule->getFoundTheseAnneeUniv(), $tau);
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_lie_avec_admission_validee(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $doctorant = $this->createMock(Doctorant::class);
        $individu = $this->createStub(Individu::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($doctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
        );
        static::assertTrue(
            $rule->canCreateTheseAnneeUniv(),
            "La règle devrait répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_liee_et_admission_non_validee(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $doctorant = $this->createMock(Doctorant::class);
        $individu = $this->createStub(Individu::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($doctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission existante
        $admission = $this->createMock(Admission::class);
        $this->admissionRepository->method('findOneByDoctorant')->willReturn($admission);

        // Admission non validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn('AUTRE CHOSE QUE VALIDE');
        $admission->method('getEtat')->willReturn($etatAdmission);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertContains(
            $msg = StatutValideeRule::ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_lie_sans_admission(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant existant avec ce code apprenant
        $doctorant = $this->createMock(Doctorant::class);
        $individu = $this->createStub(Individu::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn($doctorant);

        // These Inexistante Pour Doctorant Lie
        $this->theseRepository->method('findThesesByDoctorant')->willReturn([]);

        // Admission inexistante
        $this->admissionRepository->method('findOneByDoctorant')->willReturn(null);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertContains(
            $msg = StatutValideeRule::ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION,
            $rule->getMessages(MessageAwareInterface::WARNING),
            "La règle devrait fournir le message suivant : " . $msg
        );
        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNull($rule->getFoundAdmission());
    }

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_lie_et_admission_validee_avec_doctorant_lie(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission existante avec numero candidat
        $admission = $this->createStub(Admission::class);
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn($admission);

        // admission validée
        $etatAdmission = $this->createStub(Etat::class);
        $etatAdmission->method('getCode')->willReturn(Admission::ETAT_VALIDE);
        $admission->method('getEtat')->willReturn($etatAdmission);
        $doctorant = $this->createStub(Doctorant::class);
        $admission->method('getDoctorant')->willReturn($doctorant);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertFalse(
            $rule->canUpdateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    public function test_doctorant_non_lie_et_admission_validee_sans_doctorant_lie(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

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

        // Annee Univ existante pour la These liée
        $this->ia->method('getPeriodeAnneeUniversitaire')->willReturn(2024);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertTrue(
            $rule->canCreateDoctorant(),
            "La règle devrait répondre qu'on peut créer le doctorant"
        );
        static::assertFalse(
            $rule->canUpdateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertTrue(
            $rule->canCreateThese(),
            "La règle devrait répondre qu'on peut créer la thèse"
        );
        static::assertTrue(
            $rule->canCreateTheseAnneeUniv(),
            "La règle devrait répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertTrue(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle devrait répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNull($rule->getFoundDoctorant());
        static::assertNotNull($rule->getFoundAdmission());
    }

    public function test_doctorant_non_lie_avec_admission_non_validee_par_numero_candidat(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

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

    /**
     * @param bool $doctorantApprenantDiff Indique s'il y a ou non un diff entre les données doctorant et les données apprenant
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[TestWith([false])]
    #[TestWith([true])]
    public function test_doctorant_non_lie_mais_trouve_avec_ine_sans_admission(bool $doctorantApprenantDiff): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission inexistante avec numero candidat
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn(null);

        // doctorant trouvé avec cet INE
        $doctorant = $this->createMock(Doctorant::class);
        $individu = $this->createStub(Individu::class);
        $doctorant->method('getIndividu')->willReturn($individu);
        $this->doctorantRepository->method('findOneByIne')->willReturn($doctorant);

        // diff avec doctorant/individu lié
        if ($doctorantApprenantDiff) {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn(['nomUsuel' => ['hochon', 'hauchon']]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn(['ine' => ['1234', '1235']]);
        } else {
            $this->individuService->method('diffIndividuFromApprenant')->willReturn([]);
            $this->doctorantService->method('diffDoctorantFromApprenant')->willReturn([]);
        }

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canCreateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut créer le doctorant"
        );
        static::assertTrue(
            $rule->canUpdateDoctorant() === $doctorantApprenantDiff,
            "La règle devrait répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertTrue(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNotNull($rule->getFoundDoctorant());
        static::assertNull($rule->getFoundAdmission());
    }

    public function test_doctorant_non_lie_et_introuvable_et_sans_admission(): void
    {
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);

        // IA non encore liée à un doctorant
        $this->ia->expects(static::once())->method('getInscriptionAdministrativeDoctorant')->willReturn(null);

        // doctorant inexistant avec ce code apprenant
        $this->doctorantRepository->method('findOneByCodeApprenant')->willReturn(null);

        // admission inexistante avec numero candidat
        $this->admissionRepository->method('findOneByNumeroCandidature')->willReturn(null);

        // doctorant non trouvé avec cet INE
        $this->doctorantRepository->method('findOneByIne')->willReturn(null);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertTrue(
            $rule->canCreateDoctorant(),
            "La règle devrait répondre qu'on peut créer le doctorant"
        );
        static::assertFalse(
            $rule->canUpdateDoctorant(),
            "La règle ne devrait pas répondre qu'on peut mettre à jour le doctorant"
        );
        static::assertFalse(
            $rule->canCreateThese(),
            "La règle ne devrait pas répondre qu'on peut créer la thèse"
        );
        static::assertFalse(
            $rule->canCreateTheseAnneeUniv(),
            "La règle ne devrait pas répondre qu'on peut créer l'annéee universitaire"
        );
        static::assertFalse(
            $rule->canUpdateCodeApprenantDoctorant(),
            "La règle devrait répondre qu'on peut renseigner le code apprenant du doctorant"
        );
        static::assertFalse(
            $rule->canLinkAdmissionToDoctorant(),
            "La règle ne devrait pas répondre qu'on peut lier l'admission au doctorant"
        );
        static::assertTrue(
            $rule->canCreateLinkInscriptionAdministrativeDoctorant(),
            "La règle devrait répondre qu'on peut créer le lien IA-Doctorant"
        );
        static::assertFalse(
            $rule->canLinkTheseAnneeUnivToInscriptionAdministrative(),
            "La règle ne devrait pas répondre qu'on peut lier l'inscription à l'année universitaire"
        );

        static::assertNull($rule->getFoundDoctorant());
        static::assertNull($rule->getFoundAdmission());
    }


    protected function createRule(): StatutValideeRule
    {
        $rule = new StatutValideeRule();
        $rule->setIndividuService($this->individuService);
        $rule->setDoctorantService($this->doctorantService);
        $rule->setTheseService($this->theseService);
        $rule->setTheseAnneeUnivService($this->theseAnneeUnivService);
        $rule->setAdmissionService($this->admissionService);

        return $rule;
    }
}

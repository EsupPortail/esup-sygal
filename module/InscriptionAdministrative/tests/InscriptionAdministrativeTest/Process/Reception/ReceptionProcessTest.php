<?php

namespace InscriptionAdministrativeTest\Process\Reception;

use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuService;
use InscriptionAdministrative\Config\Reception\ReceptionConfig;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcess;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReceptionProcessTest extends TestCase
{
    private InscriptionAdministrativeReceptionProcess $process;

    private ReceptionConfig|MockObject $receptionConfig;
    private InscriptionAdministrative|MockObject $ia;
    private ReceptionRulePluginManager|MockObject $rulePluginManager;
    private IndividuService|MockObject $individuService;
    private MockObject|IndividuService $doctorantService;

    protected function setUp(): void
    {
        $this->receptionConfig = $this->createMock(ReceptionConfig::class);
        $this->individuService = $this->createMock(IndividuService::class);
        $this->doctorantService = $this->createMock(DoctorantService::class);
        $this->ia = $this->createMock(InscriptionAdministrative::class);
        $this->rulePluginManager = $this->createMock(ReceptionRulePluginManager::class);

        $em =  $this->createMock(EntityManager::class);
        $em->method('getConnection')->willReturn($this->createStub(Connection::class));

        $this->process = new InscriptionAdministrativeReceptionProcess();
        $this->process->setReceptionConfig($this->receptionConfig);
        $this->process->setIndividuService($this->individuService);
        $this->process->setDoctorantService($this->doctorantService);
        $this->process->setEntityManager($em);
        $this->process->setReceptionRulePluginManager($this->rulePluginManager);
    }

    private function createMockForRule(): MockObject|ReceptionRuleInterface
    {
        $doctorant = $this->createMock(Doctorant::class);
        $doctorant->method('getIndividu')->willReturn($this->createStub(Individu::class));

        $rule = $this->getMockBuilder(ReceptionRuleInterface::class)->getMock();
        $rule->method('getFoundDoctorant')->willReturn($doctorant);

        return $rule;
    }

    public function test_throws_exception_when_setting_ia_having_no_apprenant()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->process->setInscriptionAdministrative($this->ia);
        $this->process = new InscriptionAdministrativeReceptionProcess();
    }

    public function test_produces_sucess_result_when_nothing_is_done()
    {
        $this->ia->expects(self::once())->method('getInscriptionAdministrativeApprenant')->willReturn($this->createStub(InscriptionAdministrativeApprenant::class));

        $rule = $this->getMockBuilder(ReceptionRuleInterface::class)->getMock();
        // NB : inutile de spécifier les `$rule->method('can*')->willReturn(false)` pour toutes les méthodes `can*()`
        // car `false` est la valeur générée/retournée par défaut par le Mock.
        $this->rulePluginManager->method('getRuleForInscriptionAdministrative')->with($this->ia)->willReturn($rule);

        $this->process->setInscriptionAdministrative($this->ia);
        $this->process->run();

        $result = $this->process->getResult();
        self::assertTrue($result->isSuccess());
    }

    public function test_doctorant_est_maj_si_maj_autorisee()
    {
        $rule = $this->createMockForRule();
        $this->rulePluginManager->method('getRuleForInscriptionAdministrative')->with($this->ia)->willReturn($rule);

        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->createStub(InscriptionAdministrativeApprenant::class));

        // la règle métier dit que le doctorant peut être màj...
        $rule->method('canUpdateDoctorant')->willReturn(true);
        // ... et la màj du doctorant est autorisée d'après la config...
        $this->receptionConfig->expects(self::once())->method('getUpdateDoctorantIfPossible')->willReturn(true);
        // NB : inutile de spécifier les `$rule->method('can*')->willReturn(false)` pour toutes les méthodes `can*()`
        // car `false` est la valeur générée/retournée par défaut par le Mock.
        // ... donc la mise à jour du doctorant doit être faite.
        $this->individuService->expects(self::once())->method('updateIndividuFromApprenant');
        $this->doctorantService->expects(self::once())->method('updateDoctorantFromApprenant');

        $this->process->setInscriptionAdministrative($this->ia);
        $this->process->run();
    }

    public function test_doctorant_nest_pas_maj_si_maj_est_interdite()
    {
        $rule = $this->createMockForRule();
        $this->rulePluginManager->method('getRuleForInscriptionAdministrative')->with($this->ia)->willReturn($rule);

        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->createStub(InscriptionAdministrativeApprenant::class));

        // la règle métier dit que le doctorant peut être màj...
        $rule->method('canUpdateDoctorant')->willReturn(true);
        // ... mais la màj du doctorant est interdite d'après la config...
        $this->receptionConfig->expects(self::once())->method('getUpdateDoctorantIfPossible')->willReturn(false);
        // NB : inutile de spécifier les `$rule->method('can*')->willReturn(false)` pour toutes les méthodes `can*()`
        // car `false` est la valeur générée/retournée par défaut par le Mock.
        // ... donc la mise à jour du doctorant ne doit pas être faite.
        $this->individuService->expects(self::never())->method('updateIndividuFromApprenant');
        $this->doctorantService->expects(self::never())->method('updateDoctorantFromApprenant');

        $this->process->setInscriptionAdministrative($this->ia);
        $this->process->run();
    }
}

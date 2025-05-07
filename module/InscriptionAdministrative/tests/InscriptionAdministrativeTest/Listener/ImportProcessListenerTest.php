<?php

namespace InscriptionAdministrativeTest\Listener;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\Repository\InscriptionAdministrativeRepository;
use InscriptionAdministrative\Listener\InscriptionAdministrativeImportProcessListener;
use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcess;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SygalApiImpl\Event\InscriptionAdministrativeImportEvent;

class ImportProcessListenerTest extends TestCase
{
    private InscriptionAdministrativeImportProcessListener|MockObject $listener;
    private InscriptionAdministrativeService|MockObject $inscriptionAdministrativeService;
    private InscriptionAdministrativeReceptionProcess|MockObject $inscriptionAdministrativeProcess;
    private InscriptionAdministrativeRepository|MockObject $repository;

    protected function createImportProcessListener(): void
    {
        $this->repository = $this->createStub(InscriptionAdministrativeRepository::class);
        $this->inscriptionAdministrativeService = $this->createMock(InscriptionAdministrativeService::class);
        $this->inscriptionAdministrativeProcess = $this->createMock(InscriptionAdministrativeReceptionProcess::class);

        $this->inscriptionAdministrativeService->method('getRepository')->willReturn($this->repository);

        $this->listener = new InscriptionAdministrativeImportProcessListener();
        $this->listener->setInscriptionAdministrativeService($this->inscriptionAdministrativeService);
        $this->listener->setInscriptionAdministrativeReceptionProcess($this->inscriptionAdministrativeProcess);
    }

    public function test_throws_runtime_exception_when_event_import_event_contains_unknown_ia()
    {
        $this->createImportProcessListener();

        $event = $this->createStub(InscriptionAdministrativeImportEvent::class);

        // l'evt mentionne un source_code d'IA
        $sourceCode = 'ABCD';
        $event->method('getInscriptionAdministrativeSourceCode')->willReturn($sourceCode);
        // IA introuvable avec ce source_code code
        $this->repository->method('findOneBySourceCode')->with($sourceCode)->willReturn(null);

        $this->expectException(RuntimeException::class);

        $this->listener->onImportInscriptionAdministrative($event);
    }

    public function test_reception_process_nest_pas_lance_apres_le_process_dimport_si_ce_nest_pas_demande()
    {
        $this->createImportProcessListener();

        $event = $this->createStub(InscriptionAdministrativeImportEvent::class);
        $ia = self::createStub(InscriptionAdministrative::class);

        // l'evt mentionne un source_code d'IA
        $sourceCode = 'ABCD';
        $event->method('getInscriptionAdministrativeSourceCode')->willReturn($sourceCode);
        // IA trouvée avec ce source_code code
        $this->repository->method('findOneBySourceCode')->with($sourceCode)->willReturn($ia);

        // demande de lancement du process de reception
        $this->listener->setRunReceptionProcessAfterImport(false);
        // appel attendu à InscriptionAdministrativeReceptionProcess::run()
        $this->inscriptionAdministrativeProcess->expects($this->never())->method('run');

        $this->listener->onImportInscriptionAdministrative($event);
    }

    public function test_reception_process_est_lance_apres_le_process_dimport_si_cest_demande()
    {
        $this->createImportProcessListener();

        $event = $this->createStub(InscriptionAdministrativeImportEvent::class);
        $ia = self::createStub(InscriptionAdministrative::class);

        // l'evt mentionne un source_code d'IA
        $sourceCode = 'ABCD';
        $event->method('getInscriptionAdministrativeSourceCode')->willReturn($sourceCode);
        // IA introuvable avec ce source_code code
        $this->repository->method('findOneBySourceCode')->with($sourceCode)->willReturn($ia);

        // demande de lancement du process de reception
        $this->listener->setRunReceptionProcessAfterImport(true);
        // appel attendu à InscriptionAdministrativeReceptionProcess::run()
        $this->inscriptionAdministrativeProcess->expects($this->once())->method('run');

        $this->listener->onImportInscriptionAdministrative($event);
    }
}

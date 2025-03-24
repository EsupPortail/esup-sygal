<?php

namespace InscriptionAdministrativeTest\Listener;

use InscriptionAdministrative\Entity\Db\Repository\InscriptionAdministrativeRepository;
use InscriptionAdministrative\Listener\ImportProcessListener;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SygalApiImpl\Event\InscriptionAdministrativeImportEvent;

class ImportProcessListenerTest extends TestCase
{
    public function test_throws_runtime_exception_when_event_import_event_contains_unknown_ia()
    {
        $sourceCode = 'ABCD';

        // l'evt mentionne un source_code d'IA
        $event = $this->createStub(InscriptionAdministrativeImportEvent::class);
        $event->method('getInscriptionAdministrativeSourceCode')->willReturn($sourceCode);

        // IA introuvable avec ce source_code code
        $repo = $this->createStub(InscriptionAdministrativeRepository::class);
        $repo->method('findOneBySourceCode')->with($sourceCode)->willReturn(null);
        $inscriptionAdministrativeService = $this->createStub(InscriptionAdministrativeService::class);
        $inscriptionAdministrativeService->method('getRepository')->willReturn($repo);

        $this->expectException(RuntimeException::class);

        $listener = new ImportProcessListener();
        $listener->setInscriptionAdministrativeService($inscriptionAdministrativeService);
        $listener->onImportInscriptionAdministrative($event);
    }
}

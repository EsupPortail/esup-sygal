<?php

namespace ImportTest\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Import\Service\DbService;
use Import\Service\DbServiceJSONHelper;
use Import\Service\DbServiceSQLGenerator;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Log\LoggerInterface;

class DbServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityClassMetadata;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var DbServiceJSONHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonHelper;

    /**
     * @var DbServiceSQLGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sqlGenerator;

    /**
     * @var Etablissement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $etablissement;

    /**
     * @var DbService
     */
    private $dbService;

    /**
     *
     */
    protected function setUp()
    {
        $this->entityClassMetadata = $this->createMock(ClassMetadata::class);
        $this->entityClassMetadata->name = 'EntityClass';
        $this->entityClassMetadata->table = ['name' => 'TABLE'];
        $this->entityClassMetadata->columnNames = [
            'id' => 'ID',
            'code' => 'CODE',
        ];
        $this->entityClassMetadata->fieldMappings = [
            'id' => ['type' => 'string'],
            'code' => ['type' => 'string'],
        ];

        $this->connection = $this->createMock(Connection::class);

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager->expects($this->any())->method('getClassMetadata')->willReturn($this->entityClassMetadata);
        $this->entityManager->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->jsonHelper = $this->createMock(DbServiceJSONHelper::class);

        $this->sqlGenerator = $this->createMock(DbServiceSQLGenerator::class);

        /** @var Etablissement|\PHPUnit_Framework_MockObject_MockObject $etablissement */
        $this->etablissement = $this->createMock(Etablissement::class);
        $this->etablissement->expects($this->any())->method('getCode')->willReturn('ETAB');

        $this->dbService = new DbService();
        $this->dbService->setEntityManager($this->entityManager);
        $this->dbService->setJsonHelper($this->jsonHelper);
        $this->dbService->setSqlGenerator($this->sqlGenerator);
        $this->dbService->setLogger($this->logger);
        $this->dbService->setEtablissement($this->etablissement);
    }

    public function testSettingServiceNameResetsMetadata()
    {
        $this->entityManager->expects($this->once())->method('getClassMetadata')->willReturn('not null');

        $this->dbService->clear();
        $this->dbService->setServiceName('whatever');

        $this->assertNull($this->getObjectAttribute($this->dbService, 'entityClassMetadata'));
    }

    public function testClearDoesNotPerformAnyCommit()
    {
        $this->connection->expects($this->never())->method('commit');

        $this->dbService->clear([]);
    }

    public function testClearAddsMandatoryEtablissementFilter()
    {
        $this->sqlGenerator->expects($this->once())->method('generateSQLQueryForClearingExistingData')
            ->with('TABLE', ['critere' => 'valeur', 'etablissement_id' => 'ETAB']);

        $this->dbService->clear(['critere' => 'valeur']);
    }

    public function testClearReplacesTheseIdFilterValueByTheSourceCode()
    {
        /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);

        /** @var These|\PHPUnit_Framework_MockObject_MockObject $repository */
        $these = $this->createMock(These::class);
        $these->expects($this->once())->method('getSourceCode')->willReturn('source code');

        $this->entityManager->expects($this->once())->method('getRepository')->willReturn($repository);
        $repository->expects($this->once())->method('find')->with(12)->willReturn($these);

        $this->sqlGenerator->expects($this->once())->method('generateSQLQueryForClearingExistingData')
            ->with('TABLE', ['these_id' => 'source code', 'etablissement_id' => 'ETAB']);

        $this->dbService->clear(['these_id' => 12]);
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testClearThrowsExceptionIfNoTheseFoundWithTheseIdFilterValue()
    {
        /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);

        $this->entityManager->expects($this->once())->method('getRepository')->willReturn($repository);
        $repository->expects($this->once())->method('find')->with(12)->willReturn(null);

        $this->dbService->clear(['these_id' => 12]);
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testClearThrowsExceptionWhenQueryFails()
    {
        $this->connection->expects($this->once())->method('executeQuery')->willThrowException(new DBALException());

        $this->dbService->clear([]);
    }

    public function testSaveDoesNotPerformAnyCommit()
    {
        $this->connection->expects($this->never())->method('commit');

        $this->dbService->save([]);
    }

    public function testSaveCutsQueriesIntoChunks()
    {
        $this->dbService->setInsertQueriesChunkSize(20);

        $jsonObjects = array_fill(0, 20 + 3, new \stdClass());

//        $this->jsonHelper->expects($this->exactly($n*count($jsonObjects)))->method('extractPropertyValue')
//            ->willReturnOnConsecutiveCalls('12', 'abcd');
//
//        $this->sqlGenerator->expects($this->exactly($n*count($jsonObjects)))->method('formatValueForPropertyType')
//            ->willReturnOnConsecutiveCalls('12', 'abcd');

        $this->sqlGenerator->expects($this->exactly(23))->method('generateSQLQueryForSavingData')
            ->willReturnOnConsecutiveCalls('INSERT...');

        $this->sqlGenerator->expects($this->exactly(2))->method('wrapSQLQueriesInBeginEnd')
            ->withConsecutive($this->countOf(20), $this->countOf(3))
            ->willReturnOnConsecutiveCalls('BEGIN INSERT 1...', 'BEGIN INSERT 2...');

        $this->connection->expects($this->exactly(2))->method('executeQuery')
            ->withConsecutive(['BEGIN INSERT 1...'], ['BEGIN INSERT 2...']);

        $this->dbService->save($jsonObjects);
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testSaveThrowsExceptionWhenQueryFails()
    {
        $this->connection->expects($this->once())->method('executeQuery')->willThrowException(new DBALException());

        $this->dbService->save([new \stdClass()]);
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testCommitPerformsRollbackAndThrowsExceptionIfCommitFails()
    {
        $this->connection->expects($this->once())->method('commit')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('rollback');

        $this->dbService->commit();
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testCommitPerformsRollbackAndThrowsExceptionIfRollbackFails()
    {
        $this->connection->expects($this->once())->method('commit')->willThrowException(new \Exception());
        $this->connection->expects($this->once())->method('rollback')->willThrowException(new ConnectionException());

        $this->dbService->commit();
    }

    public function testInsertLogDoesNotPerformAnyCommit()
    {
        $this->connection->expects($this->never())->method('commit');

        /** @var DateTime $startDate */
        $startDate = $this->createMock(DateTime::class);

        $this->dbService->insertLog('', $startDate, 0, '', '');
    }
}

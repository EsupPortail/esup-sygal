<?php

namespace ImportTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Import\Service\DbService;
use Import\Service\SQLGenerator;
use Zend\Log\LoggerInterface;

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
     * @var SQLGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sqlGenerator;

    protected function setUp()
    {
        $this->entityClassMetadata = $this->createMock(ClassMetadata::class);
        $this->entityClassMetadata->name = 'EntityClass';
        $this->entityClassMetadata->table = ['name' => 'TABLE'];
        $this->entityClassMetadata->columnNames = ['one'=>'ONE', 'two'=>'TWO'];
        $this->entityClassMetadata->fieldMappings = [
            'one' => ['type' => 'string'],
            'two' => ['type' => 'string'],
        ];

        $this->connection = $this->createMock(Connection::class);

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager->expects($this->once())->method('getClassMetadata')->willReturn($this->entityClassMetadata);
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sqlGenerator = $this->createMock(SQLGenerator::class);
    }

    public function testCanSaveEntities()
    {
        $jsonEntity = new \stdClass();

        $this->entityClassMetadata->columnNames = [
            'one' => 'ONE',
            'two' => 'TWO',
        ];
        $this->entityClassMetadata->fieldMappings = [
            'one' => ['type' => 'string'],
            'two' => ['type' => 'string'],
        ];

        $service = new DbService();
        $service->save([$jsonEntity]);

    }
}

<?php

namespace ApplicationUnitTest\Service\File;

use Application\Service\File\FileService;
use Application\Service\File\FileServiceFactory;
use UnicaenApp\Exception\RuntimeException;
use Interop\Container\ContainerInterface;

class FileServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $container;

    /** @var string */
    private $rootDirPath;

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->rootDirPath = '/tmp/' . uniqid('sygal_test_');
    }

    public function testInvokeCreatesFileServiceInstance()
    {
        mkdir($this->rootDirPath, 400);
        $config = [
            'fichier' => [
                'root_dir_path' => $this->rootDirPath
            ],
        ];
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $factory = new FileServiceFactory();
        $service = $factory->__invoke($this->container);

        $this->assertInstanceOf(FileService::class, $service);
    }

    public function testInvokeThrowsExceptionWhenConfigIsNotValid()
    {
        $config = [];
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(RuntimeException::class);

        $factory = new FileServiceFactory();
        $factory->__invoke($this->container);
    }

    public function testInvokeThrowsExceptionWhenRootDirPathIsNotReadable()
    {
        $config = [
            'fichier' => [
                'root_dir_path' => '/azerty'
            ],
        ];

        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(RuntimeException::class);

        $factory = new FileServiceFactory();
        $factory->__invoke($this->container);
    }
}

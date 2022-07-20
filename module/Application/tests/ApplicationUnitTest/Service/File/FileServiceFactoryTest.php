<?php

namespace ApplicationUnitTest\Service\File;

use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\Fichier\FichierStorageServiceFactory;
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

        $factory = new FichierStorageServiceFactory();
        $service = $factory->__invoke($this->container);

        $this->assertInstanceOf(FichierStorageService::class, $service);
    }

    public function testInvokeThrowsExceptionWhenConfigIsNotValid()
    {
        $config = [];
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(RuntimeException::class);

        $factory = new FichierStorageServiceFactory();
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

        $factory = new \Fichier\Service\Fichier\FichierStorageServiceFactory();
        $factory->__invoke($this->container);
    }
}

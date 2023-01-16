<?php

namespace ApplicationUnitTest\Test\Provider;

use Notification\Service\NotifierService;
use These\Service\These\TheseService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class MockProvider
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $testCase;

    /**
     * MockProvider constructor.
     *
     * @param PHPUnit_Framework_TestCase $testCase
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @return EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    public function entityManagerMock()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|EntityManager $em */
        $em = $this->testCase->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }

    /**
     * @param string $repositoryClass
     * @return EntityRepository|PHPUnit_Framework_MockObject_MockObject
     */
    public function entityRepositoryMock($repositoryClass = null)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|EntityManager $repo */
        $repo = $this->testCase->getMockBuilder($repositoryClass ?: EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $repo;
    }

    /**
     * @param EntityRepository|PHPUnit_Framework_MockObject_MockObject|null $theseRepository
     * @return TheseService|PHPUnit_Framework_MockObject_MockObject
     */
    public function theseServiceMock($theseRepository = null)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TheseService $theseService */
        $theseService = $this->testCase->getMockBuilder(TheseService::class)->getMock();

        if ($theseRepository !== null) {
            $theseService
                ->method('getRepository')
                ->willReturn($theseRepository);
        }

        return $theseService;
    }

    /**
     * @return NotifierService|PHPUnit_Framework_MockObject_MockObject
     */
    public function notificationServiceMock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|NotifierService $service */
        $service = $this->testCase->getMockBuilder(NotifierService::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $service;
    }
}
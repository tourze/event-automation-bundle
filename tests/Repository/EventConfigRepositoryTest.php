<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\EventConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventConfigRepositoryTest extends TestCase
{
    private EventConfigRepository $repository;
    /** @var ManagerRegistry&MockObject */
    private ManagerRegistry $registry;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        // 设置 ManagerRegistry 的模拟行为
        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->with(EventConfig::class)
            ->willReturn($this->entityManager);

        // 设置 EntityManager 的模拟行为
        $classMetadata = new ClassMetadata(EventConfig::class);
        $this->entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->with(EventConfig::class)
            ->willReturn($classMetadata);

        $this->repository = new EventConfigRepository($this->registry);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(EventConfigRepository::class, $this->repository);
    }

    public function testInheritance(): void
    {
        $this->assertInstanceOf(
            '\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
            $this->repository
        );
    }

    public function testGetClassName(): void
    {
        $this->assertEquals(EventConfig::class, $this->repository->getClassName());
    }

    public function testRepositoryCanBeInstantiated(): void
    {
        // 验证仓库可以正确实例化
        $this->assertNotNull($this->repository);
        
        // 验证仓库是为正确的实体类创建的
        $this->assertEquals(EventConfig::class, $this->repository->getClassName());
    }
}
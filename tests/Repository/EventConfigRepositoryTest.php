<?php

namespace EventAutomationBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\EventConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(EventConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class EventConfigRepositoryTest extends AbstractRepositoryTestCase
{
    private EventConfigRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(EventConfigRepository::class);
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $entity = new EventConfig();
        $entity->setName('Save Test Event');
        $entity->setIdentifier('save-test-event');

        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(EventConfig::class, $savedEntity);
        $this->assertEquals('Save Test Event', $savedEntity->getName());
    }

    public function testSaveMethodWithoutFlushShouldNotPersistImmediately(): void
    {
        $entity = new EventConfig();
        $entity->setName('No Flush Test Event');
        $entity->setIdentifier('no-flush-test-event');

        $this->repository->save($entity, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $entity = new EventConfig();
        $entity->setName('Remove Test Event');
        $entity->setIdentifier('remove-test-event');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity);

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    public function testRemoveMethodWithoutFlushShouldNotDeleteImmediately(): void
    {
        $entity = new EventConfig();
        $entity->setName('No Flush Remove Test Event');
        $entity->setIdentifier('no-flush-remove-test-event');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity, false);

        $result = $this->repository->find($id);
        $this->assertInstanceOf(EventConfig::class, $result);

        self::getEntityManager()->flush();
        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    /**
     * @return EventConfigRepository
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $entity = new EventConfig();
        $entity->setName('测试事件_' . time());
        $entity->setIdentifier('test_event_' . time() . '_' . random_int(1000, 9999));
        $entity->setCronExpression('0 */5 * * * *');
        $entity->setTriggerSql('SELECT COUNT(*) FROM test_table');
        $entity->setValid(true);

        return $entity;
    }
}

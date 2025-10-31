<?php

namespace EventAutomationBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\ContextConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ContextConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class ContextConfigRepositoryTest extends AbstractRepositoryTestCase
{
    private ContextConfigRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ContextConfigRepository::class);
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new ContextConfig();
        $entity->setEventConfig($eventConfig);
        $entity->setName('test_context');
        $entity->setEntityClass('App\Entity\TestEntity');
        $entity->setQuerySql('SELECT * FROM test_table WHERE id = :id');
        $entity->setQueryParams(['id' => 1]);
        $entity->setValid(true);

        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(ContextConfig::class, $savedEntity);
        $this->assertEquals('test_context', $savedEntity->getName());
        $this->assertEquals('App\Entity\TestEntity', $savedEntity->getEntityClass());
        $this->assertEquals('SELECT * FROM test_table WHERE id = :id', $savedEntity->getQuerySql());
        $this->assertEquals(['id' => 1], $savedEntity->getQueryParams());
        $this->assertTrue($savedEntity->isValid());
    }

    public function testSaveMethodWithoutFlushShouldNotPersistImmediately(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new ContextConfig();
        $entity->setEventConfig($eventConfig);
        $entity->setName('test_context_no_flush');
        $entity->setEntityClass('App\Entity\TestEntity');
        $entity->setQuerySql('SELECT * FROM test_table');

        $this->repository->save($entity, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new ContextConfig();
        $entity->setEventConfig($eventConfig);
        $entity->setName('test_context_remove');
        $entity->setEntityClass('App\Entity\TestEntity');
        $entity->setQuerySql('SELECT * FROM test_table');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity);

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    public function testRemoveMethodWithoutFlushShouldNotDeleteImmediately(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new ContextConfig();
        $entity->setEventConfig($eventConfig);
        $entity->setName('test_context_no_flush_remove');
        $entity->setEntityClass('App\Entity\TestEntity');
        $entity->setQuerySql('SELECT * FROM test_table');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity, false);

        $result = $this->repository->find($id);
        $this->assertInstanceOf(ContextConfig::class, $result);

        self::getEntityManager()->flush();
        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    public function testFindByEventConfigShouldReturnCorrectResults(): void
    {
        $eventConfig1 = $this->createEventConfig();
        $eventConfig2 = $this->createEventConfig();

        $context1 = new ContextConfig();
        $context1->setEventConfig($eventConfig1);
        $context1->setName('context_1');
        $context1->setEntityClass('App\Entity\TestEntity1');
        $context1->setQuerySql('SELECT * FROM table1');
        $this->repository->save($context1);

        $context2 = new ContextConfig();
        $context2->setEventConfig($eventConfig2);
        $context2->setName('context_2');
        $context2->setEntityClass('App\Entity\TestEntity2');
        $context2->setQuerySql('SELECT * FROM table2');
        $this->repository->save($context2);

        $results = $this->repository->findBy(['eventConfig' => $eventConfig1]);
        $this->assertCount(1, $results);
        /** @var ContextConfig $firstResult */
        $firstResult = $results[0];
        $this->assertEquals('context_1', $firstResult->getName());
    }

    public function testFindByValidStatusShouldReturnCorrectResults(): void
    {
        $eventConfig = $this->createEventConfig();

        $validContext = new ContextConfig();
        $validContext->setEventConfig($eventConfig);
        $validContext->setName('valid_context');
        $validContext->setEntityClass('App\Entity\TestEntity');
        $validContext->setQuerySql('SELECT * FROM test_table');
        $validContext->setValid(true);
        $this->repository->save($validContext);

        $invalidContext = new ContextConfig();
        $invalidContext->setEventConfig($eventConfig);
        $invalidContext->setName('invalid_context');
        $invalidContext->setEntityClass('App\Entity\TestEntity');
        $invalidContext->setQuerySql('SELECT * FROM test_table');
        $invalidContext->setValid(false);
        $this->repository->save($invalidContext);

        $validResults = $this->repository->findBy(['valid' => true]);
        $this->assertGreaterThanOrEqual(1, count($validResults));

        $found = false;
        /** @var ContextConfig $result */
        foreach ($validResults as $result) {
            if ('valid_context' === $result->getName()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * @return ContextConfigRepository
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $eventConfig = $this->createEventConfig();

        $entity = new ContextConfig();
        $entity->setEventConfig($eventConfig);
        $entity->setName('test_context_' . time() . '_' . random_int(1000, 9999));
        $entity->setEntityClass('App\Entity\TestEntity_' . time());
        $entity->setQuerySql('SELECT * FROM test_table WHERE id = :id');
        $entity->setQueryParams(['id' => random_int(1, 1000)]);
        $entity->setValid(true);

        return $entity;
    }

    private function createEventConfig(): EventConfig
    {
        $eventConfig = new EventConfig();
        $eventConfig->setName('Test Event ' . time() . '_' . random_int(1000, 9999));
        $eventConfig->setIdentifier('test_event_' . time() . '_' . random_int(1000, 9999));
        $eventConfig->setCronExpression('0 */5 * * * *');
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM test_table');
        $eventConfig->setValid(true);

        self::getEntityManager()->persist($eventConfig);
        self::getEntityManager()->flush();

        return $eventConfig;
    }
}

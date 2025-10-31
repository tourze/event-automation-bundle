<?php

namespace EventAutomationBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;
use EventAutomationBundle\Repository\TriggerLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TriggerLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class TriggerLogRepositoryTest extends AbstractRepositoryTestCase
{
    private TriggerLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TriggerLogRepository::class);
    }

    public function testSaveMethodShouldPersistEntity(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        $entity->setContextData(['user_id' => 123, 'action' => 'test']);
        $entity->setResult('执行成功');

        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(TriggerLog::class, $savedEntity);
        /** @var TriggerLog $savedEntity */
        $this->assertEquals(['user_id' => 123, 'action' => 'test'], $savedEntity->getContextData());
        $this->assertEquals('执行成功', $savedEntity->getResult());
        $this->assertEquals($eventConfig->getId(), $savedEntity->getEventConfig()->getId());
    }

    public function testSaveMethodWithoutFlushShouldNotPersistImmediately(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        $entity->setContextData(['test' => 'data']);
        $entity->setResult('测试结果');

        $this->repository->save($entity, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        $entity->setContextData(['remove' => 'test']);
        $entity->setResult('待删除的日志');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity);

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    public function testRemoveMethodWithoutFlushShouldNotDeleteImmediately(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        $entity->setContextData(['no_flush' => 'remove_test']);
        $entity->setResult('不立即删除的日志');
        $this->repository->save($entity);

        $id = $entity->getId();
        $this->repository->remove($entity, false);

        $result = $this->repository->find($id);
        $this->assertInstanceOf(TriggerLog::class, $result);
        /** @var TriggerLog $result */
        self::getEntityManager()->flush();
        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    public function testFindByEventConfigShouldReturnCorrectResults(): void
    {
        $eventConfig1 = $this->createEventConfig();
        $eventConfig2 = $this->createEventConfig();

        $log1 = new TriggerLog();
        $log1->setEventConfig($eventConfig1);
        $log1->setContextData(['event' => 1]);
        $log1->setResult('事件1的日志');
        $this->repository->save($log1);

        $log2 = new TriggerLog();
        $log2->setEventConfig($eventConfig2);
        $log2->setContextData(['event' => 2]);
        $log2->setResult('事件2的日志');
        $this->repository->save($log2);

        $results = $this->repository->findBy(['eventConfig' => $eventConfig1]);
        $this->assertCount(1, $results);
        /** @var TriggerLog $firstResult */
        $firstResult = $results[0];
        $this->assertEquals('事件1的日志', $firstResult->getResult());
    }

    public function testFindByEventConfigOrderByCreatedAtDescShouldReturnLatestFirst(): void
    {
        $eventConfig = $this->createEventConfig();

        $log1 = new TriggerLog();
        $log1->setEventConfig($eventConfig);
        $log1->setContextData(['order' => 1]);
        $log1->setResult('第一条日志');
        $this->repository->save($log1);

        // 稍微延迟以确保时间戳不同
        usleep(10000);

        $log2 = new TriggerLog();
        $log2->setEventConfig($eventConfig);
        $log2->setContextData(['order' => 2]);
        $log2->setResult('第二条日志');
        $this->repository->save($log2);

        $results = $this->repository->findBy(
            ['eventConfig' => $eventConfig],
            ['id' => 'DESC']
        );

        $this->assertCount(2, $results);
        /** @var TriggerLog $firstResult */
        $firstResult = $results[0];
        /** @var TriggerLog $secondResult */
        $secondResult = $results[1];
        $this->assertEquals('第二条日志', $firstResult->getResult());
        $this->assertEquals('第一条日志', $secondResult->getResult());
    }

    public function testFindLatestLogsByEventConfigShouldReturnLimitedResults(): void
    {
        $eventConfig = $this->createEventConfig();

        // 创建多条日志记录
        for ($i = 1; $i <= 5; ++$i) {
            $log = new TriggerLog();
            $log->setEventConfig($eventConfig);
            $log->setContextData(['iteration' => $i]);
            $log->setResult("日志记录 #{$i}");
            $this->repository->save($log);
            usleep(1000); // 确保时间戳不同
        }

        // 获取最新的3条记录
        $results = $this->repository->findBy(
            ['eventConfig' => $eventConfig],
            ['id' => 'DESC'],
            3
        );

        $this->assertCount(3, $results);
        /** @var TriggerLog $firstResult */
        $firstResult = $results[0];
        /** @var TriggerLog $secondResult */
        $secondResult = $results[1];
        /** @var TriggerLog $thirdResult */
        $thirdResult = $results[2];
        $this->assertEquals('日志记录 #5', $firstResult->getResult());
        $this->assertEquals('日志记录 #4', $secondResult->getResult());
        $this->assertEquals('日志记录 #3', $thirdResult->getResult());
    }

    public function testEntityWithNullContextDataAndResultShouldPersist(): void
    {
        $eventConfig = $this->createEventConfig();
        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        // 不设置contextData和result，保持为null

        $this->repository->save($entity);

        $this->assertNotNull($entity->getId());
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(TriggerLog::class, $savedEntity);
        /** @var TriggerLog $savedEntity */
        $this->assertNull($savedEntity->getContextData());
        $this->assertNull($savedEntity->getResult());
    }

    /**
     * @return TriggerLogRepository
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $eventConfig = $this->createEventConfig();

        $entity = new TriggerLog();
        $entity->setEventConfig($eventConfig);
        $entity->setContextData([
            'timestamp' => time(),
            'random' => random_int(1000, 9999),
            'test_data' => 'automated_test',
        ]);
        $entity->setResult('自动化测试日志记录 - ' . date('Y-m-d H:i:s'));

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

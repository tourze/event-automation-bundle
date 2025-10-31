<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EventAutomationBundle\Controller\Admin\TriggerLogCrudController;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Repository\TriggerLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TriggerLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TriggerLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return TriggerLogCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TriggerLogCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '事件配置' => ['事件配置'];
        yield '创建时间' => ['创建时间'];
    }

    protected function getEntityFqcn(): string
    {
        return TriggerLog::class;
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Navigate to TriggerLog CRUD
        $link = $crawler->filter('a[href*="TriggerLogCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateTriggerLog(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Create an EventConfig first as it's required for TriggerLog
        $eventConfig = new EventConfig();
        $eventConfig->setName('测试事件配置');
        $eventConfig->setIdentifier('test-event-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Create TriggerLog
        $triggerLog = new TriggerLog();
        $triggerLog->setEventConfig($eventConfig);
        $triggerLog->setContextData(['test' => 'data', 'key' => 'value']);
        $triggerLog->setResult('测试执行成功');

        // Save TriggerLog directly using EntityManager
        self::getEntityManager()->persist($triggerLog);
        self::getEntityManager()->flush();

        // Verify TriggerLog was created
        $triggerLogRepository = self::getService(TriggerLogRepository::class);
        $savedTriggerLog = $triggerLogRepository->find($triggerLog->getId());
        $this->assertNotNull($savedTriggerLog);
        /** @var TriggerLog $savedTriggerLog */
        $this->assertEquals($eventConfig->getId(), $savedTriggerLog->getEventConfig()->getId());
        $this->assertEquals(['test' => 'data', 'key' => 'value'], $savedTriggerLog->getContextData());
        $this->assertEquals('测试执行成功', $savedTriggerLog->getResult());
    }

    public function testTriggerLogDataPersistence(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create test EventConfig first
        $eventConfig1 = new EventConfig();
        $eventConfig1->setName('触发日志测试配置1');
        $eventConfig1->setIdentifier('trigger-log-test-1-' . uniqid());
        $eventConfig1->setValid(true);

        $eventConfig2 = new EventConfig();
        $eventConfig2->setName('触发日志测试配置2');
        $eventConfig2->setIdentifier('trigger-log-test-2-' . uniqid());
        $eventConfig2->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig1, true);
        $eventConfigRepository->save($eventConfig2, true);

        // Create test TriggerLogs with different configurations
        $triggerLog1 = new TriggerLog();
        $triggerLog1->setEventConfig($eventConfig1);
        $triggerLog1->setContextData([
            'action' => 'create_user',
            'user_id' => 123,
            'timestamp' => '2024-01-01 10:00:00',
        ]);
        $triggerLog1->setResult('用户创建成功，发送欢迎邮件');

        $triggerLog2 = new TriggerLog();
        $triggerLog2->setEventConfig($eventConfig2);
        $triggerLog2->setContextData([
            'action' => 'order_completed',
            'order_id' => 456,
            'amount' => 299.99,
        ]);
        $triggerLog2->setResult('订单完成，已更新库存');

        // Save TriggerLogs
        self::getEntityManager()->persist($triggerLog1);
        self::getEntityManager()->persist($triggerLog2);
        self::getEntityManager()->flush();

        // Verify TriggerLogs are saved correctly
        $triggerLogRepository = self::getService(TriggerLogRepository::class);
        $savedTriggerLog1 = $triggerLogRepository->find($triggerLog1->getId());
        $this->assertNotNull($savedTriggerLog1);
        /** @var TriggerLog $savedTriggerLog1 */
        $this->assertEquals('触发日志测试配置1', $savedTriggerLog1->getEventConfig()->getName());
        $contextData1 = $savedTriggerLog1->getContextData();
        $this->assertNotNull($contextData1);
        $this->assertEquals('create_user', $contextData1['action']);
        $this->assertEquals(123, $contextData1['user_id']);
        $this->assertEquals('用户创建成功，发送欢迎邮件', $savedTriggerLog1->getResult());

        $savedTriggerLog2 = $triggerLogRepository->find($triggerLog2->getId());
        $this->assertNotNull($savedTriggerLog2);
        /** @var TriggerLog $savedTriggerLog2 */
        $this->assertEquals('触发日志测试配置2', $savedTriggerLog2->getEventConfig()->getName());
        $contextData2 = $savedTriggerLog2->getContextData();
        $this->assertNotNull($contextData2);
        $this->assertEquals('order_completed', $contextData2['action']);
        $this->assertEquals(456, $contextData2['order_id']);
        $this->assertEquals(299.99, $contextData2['amount']);
        $this->assertEquals('订单完成，已更新库存', $savedTriggerLog2->getResult());
    }

    public function testTriggerLogWithoutContextData(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create EventConfig
        $eventConfig = new EventConfig();
        $eventConfig->setName('无上下文数据测试配置');
        $eventConfig->setIdentifier('no-context-test-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Create TriggerLog without context data
        $triggerLog = new TriggerLog();
        $triggerLog->setEventConfig($eventConfig);
        $triggerLog->setResult('系统检查完成');

        self::getEntityManager()->persist($triggerLog);
        self::getEntityManager()->flush();

        // Verify TriggerLog was created correctly
        $triggerLogRepository = self::getService(TriggerLogRepository::class);
        $savedTriggerLog = $triggerLogRepository->find($triggerLog->getId());
        $this->assertNotNull($savedTriggerLog);
        /** @var TriggerLog $savedTriggerLog */
        $this->assertNull($savedTriggerLog->getContextData());
        $this->assertEquals('系统检查完成', $savedTriggerLog->getResult());
        $this->assertEquals($eventConfig->getId(), $savedTriggerLog->getEventConfig()->getId());
    }

    public function testTriggerLogStringRepresentation(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create EventConfig
        $eventConfig = new EventConfig();
        $eventConfig->setName('字符串表示测试配置');
        $eventConfig->setIdentifier('string-repr-test-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Create TriggerLog
        $triggerLog = new TriggerLog();
        $triggerLog->setEventConfig($eventConfig);

        self::getEntityManager()->persist($triggerLog);
        self::getEntityManager()->flush();

        // Test __toString method
        $expectedString = sprintf('TriggerLog #%s for %s', $triggerLog->getId(), $eventConfig->getName());
        $this->assertEquals($expectedString, (string) $triggerLog);
        $this->assertEquals($expectedString, $triggerLog->__toString());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // NEW action is disabled for TriggerLog - logs are system generated
        // Provide dummy data to satisfy DataProvider, but test will be skipped due to disabled action
        yield 'dummy' => ['dummy'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // EDIT action is disabled for TriggerLog - logs are read-only
        // Provide dummy data to satisfy DataProvider, but test will be skipped due to disabled action
        yield 'dummy' => ['dummy'];
    }
}

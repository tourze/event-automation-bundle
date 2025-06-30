<?php

namespace EventAutomationBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use EventAutomationBundle\Command\ProcessEventCommand;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Event\AutomationEvent;
use EventAutomationBundle\Exception\EventProcessingException;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessEventCommandTest extends TestCase
{
    private EventConfigRepository|MockObject $eventConfigRepository;
    private EventService|MockObject $eventService;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private Connection|MockObject $connection;
    private LoggerInterface|MockObject $logger;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->eventConfigRepository = $this->createMock(EventConfigRepository::class);
        $this->eventService = $this->createMock(EventService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $command = new ProcessEventCommand(
            $this->eventConfigRepository,
            $this->eventService,
            $this->eventDispatcher,
            $this->connection,
            $this->logger
        );

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecute_withNoValidEvents_shouldReturnSuccess(): void
    {
        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([]);

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecute_withValidEventButNoTrigger_shouldNotDispatchEvent(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig]);

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->with($eventConfig)
            ->willReturn(null); // 没有定时触发时间

        // 应该不会调用shouldTrigger
        $this->eventService->expects($this->never())
            ->method('shouldTrigger');

        // 不应该分发事件
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecute_withValidEventAndTriggerTimeButNoCondition_shouldNotDispatchEvent(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $nextTriggerTime = new \DateTimeImmutable();

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig]);

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->with($eventConfig)
            ->willReturn($nextTriggerTime);

        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->with($eventConfig)
            ->willReturn(false); // 不满足触发条件

        // 不应该分发事件
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecute_withValidEventAndTriggerConditions_shouldDispatchEvent(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $contextConfig = new ContextConfig();
        $contextConfig->setName('user');
        $contextConfig->setValid(true);
        $contextConfig->setQuerySql('SELECT * FROM users WHERE id = :id');
        $contextConfig->setQueryParams(['id' => 1]);

        $eventConfig->addContextConfig($contextConfig);

        $nextTriggerTime = new \DateTimeImmutable();

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig]);

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->with($eventConfig)
            ->willReturn($nextTriggerTime);

        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->with($eventConfig)
            ->willReturn(true);

        $resultMock = $this->createMock(Result::class);
        $resultMock->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['id' => 1, 'name' => 'John Doe']);

        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with(
                'SELECT * FROM users WHERE id = :id',
                ['id' => 1]
            )
            ->willReturn($resultMock);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (AutomationEvent $event) use ($eventConfig) {
                    $this->assertSame($eventConfig, $event->getConfig());
                    $this->assertEquals(['user' => ['id' => 1, 'name' => 'John Doe']], $event->getContext());
                    $this->assertSame('test_event', $event->getName());
                    return true;
                }),
                'test_event'
            );

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecute_withExceptionDuringProcessing_shouldLogErrorAndContinue(): void
    {
        $eventConfig1 = new EventConfig();
        $eventConfig1->setIdentifier('error_event');

        $eventConfig2 = new EventConfig();
        $eventConfig2->setIdentifier('success_event');

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig1, $eventConfig2]);

        // 使用withConsecutive代替多次at调用
        $this->eventService->expects($this->exactly(2))
            ->method('calculateNextTriggerTime')
            ->willReturnCallback(function (EventConfig $config) {
                if ($config->getIdentifier() === 'error_event') {
                    throw EventProcessingException::forEvent('error_event', 'Test exception');
                }
                return null;
            });

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error processing event error_event'));

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecute_withContextConfigError_shouldLogErrorAndContinue(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $contextConfig = new ContextConfig();
        $contextConfig->setName('user');
        $contextConfig->setValid(true);
        $contextConfig->setQuerySql('INVALID SQL');
        $contextConfig->setQueryParams([]);

        $eventConfig->addContextConfig($contextConfig);

        $nextTriggerTime = new \DateTimeImmutable();

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig]);

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->willReturn($nextTriggerTime);

        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->willReturn(true);

        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error collecting context user'));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (AutomationEvent $event) {
                    // 上下文应该是空的因为查询失败了
                    $this->assertEquals([], $event->getContext());
                    return true;
                }),
                'test_event'
            );

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}

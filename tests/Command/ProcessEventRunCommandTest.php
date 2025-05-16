<?php

namespace EventAutomationBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use EventAutomationBundle\Command\ProcessEventCommand;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Event\AutomationEvent;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessEventRunCommandTest extends TestCase
{
    private EventConfigRepository $repository;
    private EventService $eventService;
    private EventDispatcherInterface $eventDispatcher;
    private Connection $connection;
    private LoggerInterface $logger;
    private CommandTester $commandTester;
    private Application $application;
    
    protected function setUp(): void
    {
        // 创建模拟对象
        $this->repository = $this->createMock(EventConfigRepository::class);
        $this->eventService = $this->createMock(EventService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // 创建应用和命令
        $this->application = new Application();
        $command = new ProcessEventCommand(
            $this->repository,
            $this->eventService,
            $this->eventDispatcher,
            $this->connection,
            $this->logger
        );
        $this->application->add($command);
        
        // 使用CommandTester进行测试
        $this->commandTester = new CommandTester($command);
    }
    
    /**
     * 测试整个命令的执行过程
     */
    public function testCompleteCommandExecution(): void
    {
        // 1. 准备测试数据
        $event1 = new EventConfig();
        $event1->setIdentifier('daily_report');
        $event1->setName('每日报表');
        $event1->setValid(true);
        
        $event2 = new EventConfig();
        $event2->setIdentifier('hourly_check');
        $event2->setName('小时检查');
        $event2->setValid(true);
        
        $contextConfig = new ContextConfig();
        $contextConfig->setName('system');
        $contextConfig->setValid(true);
        $contextConfig->setQuerySql('SELECT version() as version');
        $contextConfig->setQueryParams([]);
        $event1->addContextConfig($contextConfig);
        
        // 2. 设置模拟行为
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$event1, $event2]);
            
        // event1应该被触发但event2不应该
        $this->eventService->expects($this->exactly(2))
            ->method('calculateNextTriggerTime')
            ->willReturnMap([
                [$event1, new \DateTimeImmutable('2023-01-01')], // 过去时间，应该触发
                [$event2, new \DateTimeImmutable('+1 day')] // 未来时间，不应该触发
            ]);
            
        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->with($event1)
            ->willReturn(true);
            
        // 3. 设置上下文查询模拟
        $resultMock = $this->createMock(Result::class);
        $resultMock->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['version' => 'MySQL 8.0']);
            
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with('SELECT version() as version', [])
            ->willReturn($resultMock);
            
        // 4. 验证是否正确触发了事件
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function(AutomationEvent $event) {
                    // 验证事件名称是否正确
                    $this->assertEquals('daily_report', $event->getName());
                    
                    // 验证上下文是否包含了查询结果
                    $context = $event->getContext();
                    $this->assertArrayHasKey('system', $context);
                    $this->assertEquals('MySQL 8.0', $context['system']['version']);
                    
                    return true;
                }),
                'daily_report'
            );
            
        // 5. 执行命令
        $this->commandTester->execute([]);
        
        // 6. 验证输出和状态码
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
    
    /**
     * 测试当所有事件配置均无效时的行为
     */
    public function testNoValidEventsFound(): void
    {
        // 设置没有有效事件配置
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([]);
            
        // 不应该调用任何服务方法
        $this->eventService->expects($this->never())
            ->method('calculateNextTriggerTime');
            
        $this->eventService->expects($this->never())
            ->method('shouldTrigger');
            
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');
            
        // 执行命令
        $this->commandTester->execute([]);
        
        // 验证状态码
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
} 
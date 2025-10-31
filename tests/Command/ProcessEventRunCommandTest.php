<?php

namespace EventAutomationBundle\Tests\Command;

use EventAutomationBundle\Command\ProcessEventCommand;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(ProcessEventCommand::class)]
#[RunTestsInSeparateProcesses]
final class ProcessEventRunCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    /** @var MockObject&EventConfigRepository */
    private EventConfigRepository $repository;

    /** @var MockObject&EventService */
    private EventService $eventService;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        // Mock business services
        $this->repository = $this->createMock(EventConfigRepository::class);
        $this->eventService = $this->createMock(EventService::class);

        // Replace services in container
        self::getContainer()->set(EventConfigRepository::class, $this->repository);
        self::getContainer()->set(EventService::class, $this->eventService);

        // Get command and create tester
        $command = self::getContainer()->get(ProcessEventCommand::class);
        $this->assertInstanceOf(ProcessEventCommand::class, $command);

        $this->commandTester = new CommandTester($command);
    }

    /**
     * 测试整个命令的执行过程 - 包含有效事件触发
     */
    public function testCompleteCommandExecution(): void
    {
        // 准备测试数据
        $event1 = new EventConfig();
        $event1->setIdentifier('daily_report');
        $event1->setName('每日报表');
        $event1->setValid(true);

        $event2 = new EventConfig();
        $event2->setIdentifier('hourly_check');
        $event2->setName('小时检查');
        $event2->setValid(true);

        // 设置模拟行为
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$event1, $event2])
        ;

        // event1应该被触发但event2不应该
        $this->eventService->expects($this->exactly(2))
            ->method('calculateNextTriggerTime')
            ->willReturnMap([
                [$event1, new \DateTimeImmutable('2023-01-01')], // 过去时间，应该触发
                [$event2, new \DateTimeImmutable('+1 day')], // 未来时间，不应该触发
            ])
        ;

        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->with($event1)
            ->willReturn(true)
        ;

        // 执行命令
        $result = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(Command::SUCCESS, $result);
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
            ->willReturn([])
        ;

        // 不应该调用任何服务方法
        $this->eventService->expects($this->never())
            ->method('calculateNextTriggerTime')
        ;

        $this->eventService->expects($this->never())
            ->method('shouldTrigger')
        ;

        // 执行命令
        $result = $this->commandTester->execute([]);

        // 验证状态码
        $this->assertEquals(Command::SUCCESS, $result);
    }
}

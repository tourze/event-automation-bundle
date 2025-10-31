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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(ProcessEventCommand::class)]
#[RunTestsInSeparateProcesses]
final class ProcessEventCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    /** @var MockObject&EventConfigRepository */
    private EventConfigRepository $eventConfigRepository;

    /** @var MockObject&EventService */
    private EventService $eventService;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        // Mock business services
        $this->eventConfigRepository = $this->createMock(EventConfigRepository::class);
        $this->eventService = $this->createMock(EventService::class);

        // Replace services in container
        self::getContainer()->set(EventConfigRepository::class, $this->eventConfigRepository);
        self::getContainer()->set(EventService::class, $this->eventService);

        // Get command and create tester
        $command = self::getContainer()->get(ProcessEventCommand::class);
        $this->assertInstanceOf(ProcessEventCommand::class, $command);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoValidEventsShouldReturnSuccess(): void
    {
        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([])
        ;

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithValidEventButNoTriggerShouldNotProcessEvent(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig])
        ;

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->with($eventConfig)
            ->willReturn(null) // 没有触发时间
        ;

        // 不应该调用shouldTrigger
        $this->eventService->expects($this->never())
            ->method('shouldTrigger')
        ;

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithValidEventButConditionNotMetShouldNotProcessEvent(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');

        $nextTriggerTime = new \DateTimeImmutable();

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig])
        ;

        $this->eventService->expects($this->once())
            ->method('calculateNextTriggerTime')
            ->with($eventConfig)
            ->willReturn($nextTriggerTime)
        ;

        $this->eventService->expects($this->once())
            ->method('shouldTrigger')
            ->with($eventConfig)
            ->willReturn(false) // 不满足触发条件
        ;

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithExceptionDuringProcessingShouldLogErrorAndContinue(): void
    {
        $eventConfig1 = new EventConfig();
        $eventConfig1->setIdentifier('error_event');

        $eventConfig2 = new EventConfig();
        $eventConfig2->setIdentifier('success_event');

        $this->eventConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([$eventConfig1, $eventConfig2])
        ;

        $this->eventService->expects($this->exactly(2))
            ->method('calculateNextTriggerTime')
            ->willReturnCallback(function (EventConfig $config) {
                if ('error_event' === $config->getIdentifier()) {
                    throw EventProcessingException::forEvent('error_event', 'Test exception');
                }

                return null;
            })
        ;

        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
    }
}

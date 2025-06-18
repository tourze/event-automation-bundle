<?php

namespace EventAutomationBundle\Command;

use Doctrine\DBAL\Connection;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Event\AutomationEvent;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Service\EventService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'event-automation:process',
    description: '处理自动化事件',
)]
class ProcessEventCommand extends Command
{
    public const NAME = 'event-automation:process';
    public function __construct(
        private readonly EventConfigRepository $eventConfigRepository,
        private readonly EventService $eventService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configs = $this->eventConfigRepository->findBy(['valid' => true]);

        foreach ($configs as $config) {
            try {
                $this->processEvent($config);
            } catch (\Throwable $e) {
                $this->logger->error(sprintf(
                    'Error processing event %s: %s',
                    $config->getIdentifier(),
                    $e->getMessage()
                ));
            }
        }

        return Command::SUCCESS;
    }

    private function processEvent(EventConfig $config): void
    {
        // 1. 检查是否到达触发时间
        $nextTriggerTime = $this->eventService->calculateNextTriggerTime($config);
        if ($nextTriggerTime === null || $nextTriggerTime > new \DateTimeImmutable()) {
            return;
        }

        // 2. 检查触发条件
        if (!$this->eventService->shouldTrigger($config)) {
            return;
        }

        // 3. 收集上下文数据
        $context = $this->collectContext($config);

        // 4. 分发事件
        $event = new AutomationEvent($config, $context);
        $this->eventDispatcher->dispatch($event, $event->getName());
    }

    /**
     * 收集上下文数据
     *
     * @return array<string, mixed>
     */
    private function collectContext(EventConfig $config): array
    {
        $context = [];

        foreach ($config->getContextConfigs() as $contextConfig) {
            if (!$contextConfig->isValid()) {
                continue;
            }

            try {
                // 执行 SQL 查询获取数据
                $stmt = $this->connection->executeQuery(
                    $contextConfig->getQuerySql(),
                    $contextConfig->getQueryParams() ?? []
                );
                $result = $stmt->fetchAssociative();

                if ($result) {
                    $context[$contextConfig->getName()] = $result;
                }
            } catch (\Throwable $e) {
                $this->logger->error(sprintf(
                    'Error collecting context %s for event %s: %s',
                    $contextConfig->getName(),
                    $config->getIdentifier(),
                    $e->getMessage()
                ));
            }
        }

        return $context;
    }
}

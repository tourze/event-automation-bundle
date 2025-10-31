<?php

namespace EventAutomationBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventService::class)]
final class EventServiceTest extends TestCase
{
    public function testCalculateNextTriggerTimeWithValidCronExpressionShouldReturnNextDate(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression('0 0 * * *'); // 每天午夜

        $result = $eventService->calculateNextTriggerTime($eventConfig);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);

        // 验证结果是未来的日期
        $this->assertGreaterThanOrEqual(new \DateTimeImmutable(), $result);

        // 验证结果是午夜时间
        $this->assertSame('00:00:00', $result->format('H:i:s'));
    }

    public function testCalculateNextTriggerTimeWithNullCronExpressionShouldReturnNull(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression(null);

        $result = $eventService->calculateNextTriggerTime($eventConfig);

        $this->assertNull($result);
    }

    public function testCalculateNextTriggerTimeWithEmptyCronExpressionShouldReturnNull(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression('');

        $result = $eventService->calculateNextTriggerTime($eventConfig);

        $this->assertNull($result);
    }

    public function testShouldTriggerWithNullTriggerSqlShouldReturnTrue(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql(null);

        $result = $eventService->shouldTrigger($eventConfig);

        $this->assertTrue($result);
    }

    public function testShouldTriggerWithEmptyTriggerSqlShouldReturnTrue(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('');

        $result = $eventService->shouldTrigger($eventConfig);

        $this->assertTrue($result);
    }

    public function testShouldTriggerWithPositiveResultShouldReturnTrue(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM users');

        $connection->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM users')
            ->willReturn(5)
        ;

        $result = $eventService->shouldTrigger($eventConfig);

        $this->assertTrue($result);
    }

    public function testShouldTriggerWithZeroResultShouldReturnFalse(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM users WHERE 1=0');

        $connection->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM users WHERE 1=0')
            ->willReturn(0)
        ;

        $result = $eventService->shouldTrigger($eventConfig);

        $this->assertFalse($result);
    }

    public function testShouldTriggerWithDatabaseErrorShouldReturnFalse(): void
    {
        $connection = $this->createMock(Connection::class);
        $eventService = new EventService($connection);

        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('INVALID SQL');

        $connection->expects($this->once())
            ->method('fetchOne')
            ->with('INVALID SQL')
            ->willThrowException(new \Exception('Database error'))
        ;

        $result = $eventService->shouldTrigger($eventConfig);

        $this->assertFalse($result);
    }
}

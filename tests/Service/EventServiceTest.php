<?php

namespace EventAutomationBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    private Connection $connection;
    private EventService $eventService;
    
    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->eventService = new EventService($this->connection);
    }
    
    public function testCalculateNextTriggerTime_withValidCronExpression_shouldReturnNextDate(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression('0 0 * * *'); // 每天午夜
        
        $result = $this->eventService->calculateNextTriggerTime($eventConfig);
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        
        // 验证结果是未来的日期
        $this->assertGreaterThanOrEqual(new \DateTimeImmutable(), $result);
        
        // 验证结果是午夜时间
        $this->assertSame('00:00:00', $result->format('H:i:s'));
    }
    
    public function testCalculateNextTriggerTime_withNullCronExpression_shouldReturnNull(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression(null);
        
        $result = $this->eventService->calculateNextTriggerTime($eventConfig);
        
        $this->assertNull($result);
    }
    
    public function testCalculateNextTriggerTime_withEmptyCronExpression_shouldReturnNull(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setCronExpression('');
        
        $result = $this->eventService->calculateNextTriggerTime($eventConfig);
        
        $this->assertNull($result);
    }
    
    public function testShouldTrigger_withNullTriggerSql_shouldReturnTrue(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql(null);
        
        $result = $this->eventService->shouldTrigger($eventConfig);
        
        $this->assertTrue($result);
    }
    
    public function testShouldTrigger_withEmptyTriggerSql_shouldReturnTrue(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('');
        
        $result = $this->eventService->shouldTrigger($eventConfig);
        
        $this->assertTrue($result);
    }
    
    public function testShouldTrigger_withPositiveResult_shouldReturnTrue(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM users');
        
        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM users')
            ->willReturn(5);
        
        $result = $this->eventService->shouldTrigger($eventConfig);
        
        $this->assertTrue($result);
    }
    
    public function testShouldTrigger_withZeroResult_shouldReturnFalse(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM users WHERE 1=0');
        
        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM users WHERE 1=0')
            ->willReturn(0);
        
        $result = $this->eventService->shouldTrigger($eventConfig);
        
        $this->assertFalse($result);
    }
    
    public function testShouldTrigger_withDatabaseError_shouldReturnFalse(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setTriggerSql('INVALID SQL');
        
        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with('INVALID SQL')
            ->willThrowException(new \Exception('Database error'));
        
        $result = $this->eventService->shouldTrigger($eventConfig);
        
        $this->assertFalse($result);
    }
} 
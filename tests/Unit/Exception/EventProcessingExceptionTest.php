<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Unit\Exception;

use EventAutomationBundle\Exception\EventProcessingException;
use PHPUnit\Framework\TestCase;

class EventProcessingExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new EventProcessingException('Test message');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
    
    public function testForEventCreatesExceptionWithFormattedMessage(): void
    {
        $eventIdentifier = 'test.event';
        $message = 'Something went wrong';
        
        $exception = EventProcessingException::forEvent($eventIdentifier, $message);
        
        $expectedMessage = sprintf('Event processing failed for "%s": %s', $eventIdentifier, $message);
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
    
    public function testForEventWithPreviousException(): void
    {
        $eventIdentifier = 'test.event';
        $message = 'Something went wrong';
        $previousException = new \Exception('Previous error');
        
        $exception = EventProcessingException::forEvent($eventIdentifier, $message, $previousException);
        
        $this->assertSame($previousException, $exception->getPrevious());
    }
    
    public function testForEventWithoutPreviousException(): void
    {
        $eventIdentifier = 'test.event';
        $message = 'Something went wrong';
        
        $exception = EventProcessingException::forEvent($eventIdentifier, $message);
        
        $this->assertNull($exception->getPrevious());
    }
}
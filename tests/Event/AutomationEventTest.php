<?php

namespace EventAutomationBundle\Tests\Event;

use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Event\AutomationEvent;
use PHPUnit\Framework\TestCase;

class AutomationEventTest extends TestCase
{
    private EventConfig $eventConfig;
    private array $context;
    private AutomationEvent $event;
    
    protected function setUp(): void
    {
        $this->eventConfig = new EventConfig();
        $this->eventConfig->setIdentifier('test_event');
        $this->eventConfig->setName('测试事件');
        
        $this->context = [
            'user' => ['id' => 1, 'name' => 'John Doe'],
            'order' => ['id' => 123, 'total' => 99.99]
        ];
        
        $this->event = new AutomationEvent($this->eventConfig, $this->context);
    }
    
    public function testGetConfig_shouldReturnConfig(): void
    {
        $this->assertSame($this->eventConfig, $this->event->getConfig());
    }
    
    public function testGetContext_shouldReturnContext(): void
    {
        $this->assertSame($this->context, $this->event->getContext());
    }
    
    public function testGetName_shouldReturnEventIdentifier(): void
    {
        $this->assertSame('test_event', $this->event->getName());
    }
    
    public function testConstructWithEmptyContext_shouldSetEmptyArray(): void
    {
        $event = new AutomationEvent($this->eventConfig);
        $this->assertSame([], $event->getContext());
    }
    
    public function testGetName_shouldReturnCorrectEventNameWhenIdentifierChanged(): void
    {
        // 测试当标识符更改时，getName也会返回更新后的值
        $this->eventConfig->setIdentifier('updated_event');
        $this->assertSame('updated_event', $this->event->getName());
    }
} 
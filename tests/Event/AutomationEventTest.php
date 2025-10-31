<?php

namespace EventAutomationBundle\Tests\Event;

use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Event\AutomationEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AutomationEvent::class)]
final class AutomationEventTest extends AbstractEventTestCase
{
    public function testGetConfigShouldReturnConfig(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');
        $eventConfig->setName('测试事件');

        $context = [
            'user' => ['id' => 1, 'name' => 'John Doe'],
            'order' => ['id' => 123, 'total' => 99.99],
        ];

        $event = new AutomationEvent($eventConfig, $context);

        $this->assertSame($eventConfig, $event->getConfig());
    }

    public function testGetContextShouldReturnContext(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');
        $eventConfig->setName('测试事件');

        $context = [
            'user' => ['id' => 1, 'name' => 'John Doe'],
            'order' => ['id' => 123, 'total' => 99.99],
        ];

        $event = new AutomationEvent($eventConfig, $context);

        $this->assertSame($context, $event->getContext());
    }

    public function testGetNameShouldReturnEventIdentifier(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');
        $eventConfig->setName('测试事件');

        $context = [
            'user' => ['id' => 1, 'name' => 'John Doe'],
            'order' => ['id' => 123, 'total' => 99.99],
        ];

        $event = new AutomationEvent($eventConfig, $context);

        $this->assertSame('test_event', $event->getName());
    }

    public function testConstructWithEmptyContextShouldSetEmptyArray(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');
        $eventConfig->setName('测试事件');

        $event = new AutomationEvent($eventConfig);
        $this->assertSame([], $event->getContext());
    }

    public function testGetNameShouldReturnCorrectEventNameWhenIdentifierChanged(): void
    {
        // 测试当标识符更改时，getName也会返回更新后的值
        $eventConfig = new EventConfig();
        $eventConfig->setIdentifier('test_event');
        $eventConfig->setName('测试事件');

        $context = [
            'user' => ['id' => 1, 'name' => 'John Doe'],
            'order' => ['id' => 123, 'total' => 99.99],
        ];

        $event = new AutomationEvent($eventConfig, $context);

        $eventConfig->setIdentifier('updated_event');
        $this->assertSame('updated_event', $event->getName());
    }
}

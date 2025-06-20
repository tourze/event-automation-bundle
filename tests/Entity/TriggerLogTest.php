<?php

namespace EventAutomationBundle\Tests\Entity;

use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;
use PHPUnit\Framework\TestCase;

class TriggerLogTest extends TestCase
{
    private TriggerLog $triggerLog;

    protected function setUp(): void
    {
        $this->triggerLog = new TriggerLog();
    }

    public function testGettersAndSetters_shouldWorkCorrectly(): void
    {
        // 测试事件配置设置与获取
        $eventConfig = new EventConfig();
        $this->triggerLog->setEventConfig($eventConfig);
        $this->assertSame($eventConfig, $this->triggerLog->getEventConfig());

        // 测试上下文数据设置与获取
        $contextData = ['user_id' => 123, 'order_id' => 456];
        $this->triggerLog->setContextData($contextData);
        $this->assertSame($contextData, $this->triggerLog->getContextData());

        // 测试结果设置与获取
        $result = '事件处理成功';
        $this->triggerLog->setResult($result);
        $this->assertSame($result, $this->triggerLog->getResult());

        // 测试创建人设置与获取
        $createdBy = 'system';
        $this->triggerLog->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $this->triggerLog->getCreatedBy());

        // 测试创建时间设置与获取
        $createTime = new \DateTimeImmutable();
        $this->triggerLog->setCreateTime($createTime);
        $this->assertSame($createTime, $this->triggerLog->getCreateTime());

        // 测试更新时间设置与获取
        $updateTime = new \DateTimeImmutable();
        $this->triggerLog->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->triggerLog->getUpdateTime());
    }

    public function testNullableFields_shouldAcceptNull(): void
    {
        // 测试可空字段允许空值
        $this->triggerLog->setContextData(null);
        $this->assertNull($this->triggerLog->getContextData());

        $this->triggerLog->setResult(null);
        $this->assertNull($this->triggerLog->getResult());

        $this->triggerLog->setCreatedBy(null);
        $this->assertNull($this->triggerLog->getCreatedBy());

        $this->triggerLog->setCreateTime(null);
        $this->assertNull($this->triggerLog->getCreateTime());

        $this->triggerLog->setUpdateTime(null);
        $this->assertNull($this->triggerLog->getUpdateTime());
    }

    public function testGetId_shouldReturnNullWhenNew(): void
    {
        // ID应该为0，因为新创建的对象尚未持久化
        $this->assertSame(0, $this->triggerLog->getId());
    }
}

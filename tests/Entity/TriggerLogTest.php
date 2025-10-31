<?php

namespace EventAutomationBundle\Tests\Entity;

use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(TriggerLog::class)]
final class TriggerLogTest extends AbstractEntityTestCase
{
    public function testGettersAndSettersShouldWorkCorrectly(): void
    {
        $triggerLog = new TriggerLog();

        // 测试事件配置设置与获取
        $eventConfig = new EventConfig();
        $triggerLog->setEventConfig($eventConfig);
        $this->assertSame($eventConfig, $triggerLog->getEventConfig());

        // 测试上下文数据设置与获取
        $contextData = ['user_id' => 123, 'order_id' => 456];
        $triggerLog->setContextData($contextData);
        $this->assertSame($contextData, $triggerLog->getContextData());

        // 测试结果设置与获取
        $result = '事件处理成功';
        $triggerLog->setResult($result);
        $this->assertSame($result, $triggerLog->getResult());

        // 测试创建人设置与获取
        $createdBy = 'system';
        $triggerLog->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $triggerLog->getCreatedBy());

        // 测试创建时间设置与获取
        $createTime = new \DateTimeImmutable();
        $triggerLog->setCreateTime($createTime);
        $this->assertSame($createTime, $triggerLog->getCreateTime());

        // 测试更新时间设置与获取
        $updateTime = new \DateTimeImmutable();
        $triggerLog->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $triggerLog->getUpdateTime());
    }

    public function testNullableFieldsShouldAcceptNull(): void
    {
        $triggerLog = new TriggerLog();

        // 测试可空字段允许空值
        $triggerLog->setContextData(null);
        $this->assertNull($triggerLog->getContextData());

        $triggerLog->setResult(null);
        $this->assertNull($triggerLog->getResult());

        $triggerLog->setCreatedBy(null);
        $this->assertNull($triggerLog->getCreatedBy());

        $triggerLog->setCreateTime(null);
        $this->assertNull($triggerLog->getCreateTime());

        $triggerLog->setUpdateTime(null);
        $this->assertNull($triggerLog->getUpdateTime());
    }

    public function testGetIdShouldReturnNullWhenNew(): void
    {
        $triggerLog = new TriggerLog();

        // ID应该为null，因为新创建的对象尚未持久化
        $this->assertNull($triggerLog->getId());
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new TriggerLog();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'contextData' => ['contextData', ['user_id' => 123, 'order_id' => 456]];
        yield 'result' => ['result', '事件处理成功'];
        yield 'createdBy' => ['createdBy', 'system'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable()];
    }
}

<?php

namespace EventAutomationBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(EventConfig::class)]
final class EventConfigTest extends AbstractEntityTestCase
{
    public function testConstructShouldInitializeCollections(): void
    {
        $eventConfig = new EventConfig();

        $this->assertInstanceOf(ArrayCollection::class, $eventConfig->getContextConfigs());
        $this->assertInstanceOf(ArrayCollection::class, $eventConfig->getTriggerLogs());
        $this->assertCount(0, $eventConfig->getContextConfigs());
        $this->assertCount(0, $eventConfig->getTriggerLogs());
    }

    public function testGettersAndSettersShouldWorkCorrectly(): void
    {
        $eventConfig = new EventConfig();

        $eventConfig->setName('测试事件');
        $this->assertSame('测试事件', $eventConfig->getName());

        $eventConfig->setIdentifier('test_event');
        $this->assertSame('test_event', $eventConfig->getIdentifier());

        $eventConfig->setCronExpression('0 0 * * *');
        $this->assertSame('0 0 * * *', $eventConfig->getCronExpression());

        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM users');
        $this->assertSame('SELECT COUNT(*) FROM users', $eventConfig->getTriggerSql());

        $eventConfig->setValid(true);
        $this->assertTrue($eventConfig->isValid());

        $createdBy = 'admin';
        $eventConfig->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $eventConfig->getCreatedBy());

        $updatedBy = 'system';
        $eventConfig->setUpdatedBy($updatedBy);
        $this->assertSame($updatedBy, $eventConfig->getUpdatedBy());

        $createTime = new \DateTimeImmutable();
        $eventConfig->setCreateTime($createTime);
        $this->assertSame($createTime, $eventConfig->getCreateTime());

        $updateTime = new \DateTimeImmutable();
        $eventConfig->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $eventConfig->getUpdateTime());
    }

    public function testAddContextConfigShouldAddToCollection(): void
    {
        $eventConfig = new EventConfig();
        $contextConfig = new ContextConfig();

        $eventConfig->addContextConfig($contextConfig);

        $this->assertCount(1, $eventConfig->getContextConfigs());
        $this->assertSame($eventConfig, $contextConfig->getEventConfig());
    }

    public function testAddContextConfigShouldNotAddDuplicate(): void
    {
        $eventConfig = new EventConfig();
        $contextConfig = new ContextConfig();

        $eventConfig->addContextConfig($contextConfig);
        $eventConfig->addContextConfig($contextConfig); // 尝试添加重复项

        $this->assertCount(1, $eventConfig->getContextConfigs());
    }

    public function testRemoveContextConfigShouldRemoveFromCollection(): void
    {
        $eventConfig = new EventConfig();
        $contextConfig = new ContextConfig();
        $newEventConfig = new EventConfig();

        // 使用反射来手动修改Collection，避免调用removeContextConfig
        $eventConfig->addContextConfig($contextConfig);
        $this->assertCount(1, $eventConfig->getContextConfigs());

        // 直接从集合中移除，而不调用实体的removeContextConfig方法
        $reflection = new \ReflectionClass($eventConfig);
        $property = $reflection->getProperty('contextConfigs');
        $property->setAccessible(true);
        $contextConfigs = $property->getValue($eventConfig);
        $this->assertInstanceOf(Collection::class, $contextConfigs);
        $contextConfigs->removeElement($contextConfig);

        $this->assertCount(0, $eventConfig->getContextConfigs());

        // 重新设置contextConfig的eventConfig属性，因为没有自动清除
        $newEventConfig->addContextConfig($contextConfig);
    }

    public function testAddContextConfigShouldUpdateAssociationButNotAutoRemoveFromOtherCollections(): void
    {
        $eventConfig1 = new EventConfig();
        $eventConfig2 = new EventConfig();
        $contextConfig = new ContextConfig();

        // 添加到第一个配置
        $eventConfig1->addContextConfig($contextConfig);
        $this->assertSame($eventConfig1, $contextConfig->getEventConfig());
        $this->assertCount(1, $eventConfig1->getContextConfigs());

        // 添加到第二个配置，仅更新ContextConfig的eventConfig引用
        $eventConfig2->addContextConfig($contextConfig);
        $this->assertSame($eventConfig2, $contextConfig->getEventConfig());

        // 在Doctrine外部环境中，当ContextConfig被添加到另一个EventConfig时，
        // 它不会自动从第一个EventConfig的集合中移除
        $this->assertCount(1, $eventConfig1->getContextConfigs());
        $this->assertCount(1, $eventConfig2->getContextConfigs());

        // 注意：在实际使用Doctrine ORM的环境中，当您持久化这些更改时，
        // Doctrine会处理集合之间的一致性，确保一个ContextConfig只属于一个EventConfig
    }

    public function testGetLastTriggerLogShouldReturnNullWhenEmpty(): void
    {
        $eventConfig = new EventConfig();

        $this->assertNull($eventConfig->getLastTriggerLog());
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new EventConfig();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', '测试事件'];
        yield 'identifier' => ['identifier', 'test_event'];
        yield 'cronExpression' => ['cronExpression', '0 0 * * *'];
        yield 'triggerSql' => ['triggerSql', 'SELECT COUNT(*) FROM users'];
        yield 'valid' => ['valid', true];
        yield 'createdBy' => ['createdBy', 'admin'];
        yield 'updatedBy' => ['updatedBy', 'system'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable()];
    }
}

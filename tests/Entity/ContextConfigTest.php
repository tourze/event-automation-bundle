<?php

namespace EventAutomationBundle\Tests\Entity;

use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(ContextConfig::class)]
final class ContextConfigTest extends AbstractEntityTestCase
{
    public function testGettersAndSettersShouldWorkCorrectly(): void
    {
        $contextConfig = new ContextConfig();

        // 测试名称设置与获取
        $name = 'user_context';
        $contextConfig->setName($name);
        $this->assertSame($name, $contextConfig->getName());

        // 测试实体类名设置与获取
        $entityClass = 'App\Entity\User';
        $contextConfig->setEntityClass($entityClass);
        $this->assertSame($entityClass, $contextConfig->getEntityClass());

        // 测试查询SQL设置与获取
        $querySql = 'SELECT * FROM users WHERE id = :id';
        $contextConfig->setQuerySql($querySql);
        $this->assertSame($querySql, $contextConfig->getQuerySql());

        // 测试查询参数设置与获取
        $queryParams = ['id' => 1];
        $contextConfig->setQueryParams($queryParams);
        $this->assertSame($queryParams, $contextConfig->getQueryParams());

        // 测试有效性设置与获取
        $contextConfig->setValid(true);
        $this->assertTrue($contextConfig->isValid());

        // 测试创建人设置与获取
        $createdBy = 'admin';
        $contextConfig->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $contextConfig->getCreatedBy());

        // 测试更新人设置与获取
        $updatedBy = 'system';
        $contextConfig->setUpdatedBy($updatedBy);
        $this->assertSame($updatedBy, $contextConfig->getUpdatedBy());

        // 测试创建时间设置与获取
        $createTime = new \DateTimeImmutable();
        $contextConfig->setCreateTime($createTime);
        $this->assertSame($createTime, $contextConfig->getCreateTime());

        // 测试更新时间设置与获取
        $updateTime = new \DateTimeImmutable();
        $contextConfig->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $contextConfig->getUpdateTime());
    }

    public function testSetEventConfigShouldSetEventConfig(): void
    {
        $contextConfig = new ContextConfig();
        $eventConfig = new EventConfig();

        $contextConfig->setEventConfig($eventConfig);

        $this->assertSame($eventConfig, $contextConfig->getEventConfig());
    }

    public function testNullableQueryParamsShouldAllowNull(): void
    {
        $contextConfig = new ContextConfig();
        $contextConfig->setQueryParams(null);
        $this->assertNull($contextConfig->getQueryParams());
    }

    public function testNullableValidShouldAllowNull(): void
    {
        $contextConfig = new ContextConfig();
        $contextConfig->setValid(null);
        $this->assertNull($contextConfig->isValid());
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new ContextConfig();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'user_context'];
        yield 'entityClass' => ['entityClass', 'App\Entity\User'];
        yield 'querySql' => ['querySql', 'SELECT * FROM users WHERE id = :id'];
        yield 'queryParams' => ['queryParams', ['id' => 1]];
        yield 'valid' => ['valid', true];
        yield 'createdBy' => ['createdBy', 'admin'];
        yield 'updatedBy' => ['updatedBy', 'system'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable()];
    }
}

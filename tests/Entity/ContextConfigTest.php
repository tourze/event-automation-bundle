<?php

namespace EventAutomationBundle\Tests\Entity;

use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use PHPUnit\Framework\TestCase;

class ContextConfigTest extends TestCase
{
    private ContextConfig $contextConfig;
    
    protected function setUp(): void
    {
        $this->contextConfig = new ContextConfig();
    }
    
    public function testGettersAndSetters_shouldWorkCorrectly(): void
    {
        // 测试名称设置与获取
        $name = 'user_context';
        $this->contextConfig->setName($name);
        $this->assertSame($name, $this->contextConfig->getName());
        
        // 测试实体类名设置与获取
        $entityClass = 'App\Entity\User';
        $this->contextConfig->setEntityClass($entityClass);
        $this->assertSame($entityClass, $this->contextConfig->getEntityClass());
        
        // 测试查询SQL设置与获取
        $querySql = 'SELECT * FROM users WHERE id = :id';
        $this->contextConfig->setQuerySql($querySql);
        $this->assertSame($querySql, $this->contextConfig->getQuerySql());
        
        // 测试查询参数设置与获取
        $queryParams = ['id' => 1];
        $this->contextConfig->setQueryParams($queryParams);
        $this->assertSame($queryParams, $this->contextConfig->getQueryParams());
        
        // 测试有效性设置与获取
        $this->contextConfig->setValid(true);
        $this->assertTrue($this->contextConfig->isValid());
        
        // 测试创建人设置与获取
        $createdBy = 'admin';
        $this->contextConfig->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $this->contextConfig->getCreatedBy());
        
        // 测试更新人设置与获取
        $updatedBy = 'system';
        $this->contextConfig->setUpdatedBy($updatedBy);
        $this->assertSame($updatedBy, $this->contextConfig->getUpdatedBy());
        
        // 测试创建时间设置与获取
        $createTime = new \DateTimeImmutable();
        $this->contextConfig->setCreateTime($createTime);
        $this->assertSame($createTime, $this->contextConfig->getCreateTime());
        
        // 测试更新时间设置与获取
        $updateTime = new \DateTimeImmutable();
        $this->contextConfig->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->contextConfig->getUpdateTime());
    }
    
    public function testSetEventConfig_shouldSetEventConfig(): void
    {
        $eventConfig = new EventConfig();
        
        $this->contextConfig->setEventConfig($eventConfig);
        
        $this->assertSame($eventConfig, $this->contextConfig->getEventConfig());
    }
    
    public function testNullableQueryParams_shouldAllowNull(): void
    {
        $this->contextConfig->setQueryParams(null);
        $this->assertNull($this->contextConfig->getQueryParams());
    }
    
    public function testNullableValid_shouldAllowNull(): void
    {
        $this->contextConfig->setValid(null);
        $this->assertNull($this->contextConfig->isValid());
    }
} 
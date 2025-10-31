# EventAutomationBundle

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![PHP 版本](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![许可证](https://img.shields.io/packagist/l/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![总下载量](https://img.shields.io/packagist/dt/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions/workflows/ci.yml)
[![代码覆盖率](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

基于 Symfony 的事件自动化处理包，支持可配置的触发器和上下文数据收集。

## 功能特性

- **Cron 定时调度**：使用标准 cron 表达式定义事件
- **SQL 触发器**：基于数据库查询的条件触发
- **上下文数据收集**：灵活的参数化查询数据收集
- **事件日志记录**：完整的事件执行审计跟踪
- **Symfony 集成**：与 Symfony EventDispatcher 原生集成
- **多种触发类型**：支持时间和条件两种触发方式

## 系统要求

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM 3.0 或更高版本

## 安装

```bash
composer require tourze/event-automation-bundle
```

## 快速开始

### 1. 创建事件配置

```php
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\ContextConfig;

$eventConfig = new EventConfig();
$eventConfig->setName('订单超时检查')
    ->setIdentifier('order_timeout_check')
    ->setCronExpression('0 * * * *')  // 每小时执行一次
    ->setTriggerSql('SELECT COUNT(*) FROM orders WHERE status = "pending" AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)');

$contextConfig = new ContextConfig();
$contextConfig->setName('timeout_orders')
    ->setEntityClass('App\\Entity\\Order')
    ->setQuerySql('SELECT * FROM orders WHERE status = "pending" AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)')
    ->setEventConfig($eventConfig);

$entityManager->persist($eventConfig);
$entityManager->persist($contextConfig);
$entityManager->flush();
```

### 2. 创建事件监听器

```php
use EventAutomationBundle\Event\AutomationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderTimeoutEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'order_timeout_check' => 'onOrderTimeout',
        ];
    }

    public function onOrderTimeout(AutomationEvent $event): void
    {
        $context = $event->getContext();
        $timeoutOrders = $context['timeout_orders'] ?? [];
        
        // 处理超时订单...
        foreach ($timeoutOrders as $order) {
            // 订单超时处理逻辑
        }
    }
}
```

### 3. 处理事件

```bash
# 运行自动化事件
php bin/console event-automation:process
```

## 高级用法

### 手动触发事件

```php
use EventAutomationBundle\Event\AutomationEvent;

$event = new AutomationEvent($eventConfig, ['manual_trigger' => true]);
$eventDispatcher->dispatch($event, $event->getName());
```

### 复杂上下文数据

```php
$contextConfig = new ContextConfig();
$contextConfig->setName('user_stats')
    ->setEntityClass('App\\Entity\\User')
    ->setQuerySql('SELECT u.* FROM users u WHERE u.last_login < :cutoff_date')
    ->setQueryParams(['cutoff_date' => '30 days ago'])
    ->setEventConfig($eventConfig);
```

## 实体说明

### EventConfig 事件配置
主要配置实体，包含：
- `name`：事件显示名称
- `identifier`：唯一事件标识符
- `cronExpression`：Cron 调度表达式
- `triggerSql`：触发条件 SQL
- `contextConfigs`：关联的上下文配置
- `triggerLogs`：执行历史记录

### ContextConfig 上下文配置
上下文数据配置，包含：
- `name`：上下文变量名
- `entityClass`：目标实体类
- `querySql`：数据收集查询
- `queryParams`：查询参数配置

### TriggerLog 触发日志
执行日志，包含：
- `contextData`：捕获的上下文数据
- `result`：执行结果/状态

## 最佳实践

1. **SQL 性能**：确保触发 SQL 查询已优化并使用适当的索引
2. **Cron 表达式**：使用标准 cron 格式进行调度
3. **幂等性**：设计事件处理器为幂等的，可重复执行
4. **错误处理**：在事件订阅器中实现适当的错误处理
5. **监控**：为事件执行失败设置监控

## 贡献

请参阅 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

MIT 许可证。更多信息请参阅 [许可证文件](LICENSE)。
# EventAutomationBundle

EventAutomationBundle 是一个基于 Symfony 的事件自动化处理系统，它允许通过配置的方式定义事件触发条件和处理逻辑。

## 依赖

本 Bundle 依赖以下 Bundle：
- AmisBundle：提供后台管理界面支持

## 核心功能

### 1. 事件配置管理
- 支持通过 Cron 表达式定义定时触发
- 支持通过 SQL 定义触发条件
- 灵活的上下文数据收集配置
- 完整的事件触发日志记录

### 2. 上下文数据管理
- 支持多个上下文数据源配置
- SQL 查询参数配置
- 实体关联支持
- 自动数据收集

### 3. 事件处理
- 基于 Symfony EventDispatcher
- 支持异步处理
- 错误处理和日志记录
- 事件执行状态追踪

## 实体

### EventConfig 实体
事件配置实体，包含以下主要字段：
- `name`: 事件名称
- `identifier`: 事件标识符
- `cronExpression`: Cron 表达式，用于定时触发
- `triggerSql`: 触发条件 SQL
- `contextConfigs`: 关联的上下文配置
- `triggerLogs`: 触发日志记录

### ContextConfig 实体
上下文配置实体，包含以下主要字段：
- `name`: 上下文变量名
- `entityClass`: 实体类名
- `querySql`: 查询 SQL
- `queryParams`: 查询参数配置

### TriggerLog 实体
触发日志实体，包含以下主要字段：
- `contextData`: 触发时的上下文数据
- `result`: 执行结果

## 使用示例

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
    }
}
```

### 3. 手动触发事件

```php
use EventAutomationBundle\Event\AutomationEvent;

$event = new AutomationEvent($eventConfig, ['manual_trigger' => true]);
$eventDispatcher->dispatch($event, $event->getName());
```

## 命令行工具

处理自动化事件的命令：
```bash
php bin/console event-automation:process
```

## 注意事项

1. SQL 触发条件应该返回数字结果，大于 0 表示满足触发条件
2. Cron 表达式需要符合标准格式
3. 上下文查询性能要注意优化，避免大量数据查询
4. 事件处理器要保证幂等性，同一事件可能被多次触发
5. 建议配置监控，及时发现事件处理异常

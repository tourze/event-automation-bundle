# EventAutomationBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![License](https://img.shields.io/packagist/l/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/event-automation-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/event-automation-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle that provides automated event processing system with configurable triggers and context data collection.

## Features

- **Cron-based scheduling**: Define events using standard cron expressions
- **SQL-based triggers**: Conditional event triggers based on database queries  
- **Context data collection**: Flexible data gathering with parameterized queries
- **Event logging**: Complete audit trail of all event executions
- **Symfony integration**: Native integration with Symfony EventDispatcher
- **Multiple trigger types**: Support for both time-based and condition-based triggers

## Requirements

- PHP 8.1 or higher
- Symfony 7.3 or higher
- Doctrine ORM 3.0 or higher

## Installation

```bash
composer require tourze/event-automation-bundle
```

## Quick Start

### 1. Create Event Configuration

```php
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\ContextConfig;

$eventConfig = new EventConfig();
$eventConfig->setName('Order Timeout Check')
    ->setIdentifier('order_timeout_check')
    ->setCronExpression('0 * * * *')  // Run every hour
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

### 2. Create Event Listener

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
        
        // Handle timeout orders...
        foreach ($timeoutOrders as $order) {
            // Process order timeout logic
        }
    }
}
```

### 3. Process Events

```bash
# Run automation events
php bin/console event-automation:process
```

## Advanced Usage

### Manual Event Triggering

```php
use EventAutomationBundle\Event\AutomationEvent;

$event = new AutomationEvent($eventConfig, ['manual_trigger' => true]);
$eventDispatcher->dispatch($event, $event->getName());
```

### Complex Context Data

```php
$contextConfig = new ContextConfig();
$contextConfig->setName('user_stats')
    ->setEntityClass('App\\Entity\\User')
    ->setQuerySql('SELECT u.* FROM users u WHERE u.last_login < :cutoff_date')
    ->setQueryParams(['cutoff_date' => '30 days ago'])
    ->setEventConfig($eventConfig);
```

## Entities

### EventConfig
Main configuration entity with:
- `name`: Event display name
- `identifier`: Unique event identifier
- `cronExpression`: Cron scheduling expression
- `triggerSql`: SQL condition for triggering
- `contextConfigs`: Associated context configurations
- `triggerLogs`: Execution history

### ContextConfig
Context data configuration with:
- `name`: Context variable name
- `entityClass`: Target entity class
- `querySql`: Data collection query
- `queryParams`: Query parameter configuration

### TriggerLog
Execution log with:
- `contextData`: Captured context data
- `result`: Execution result/status

## Best Practices

1. **SQL Performance**: Ensure trigger SQL queries are optimized and use proper indexes
2. **Cron Expressions**: Use standard cron format for scheduling
3. **Idempotency**: Design event handlers to be idempotent for repeated executions
4. **Error Handling**: Implement proper error handling in event subscribers
5. **Monitoring**: Set up monitoring for event execution failures

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
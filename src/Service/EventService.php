<?php

namespace EventAutomationBundle\Service;

use Cron\CronExpression;
use Doctrine\DBAL\Connection;
use EventAutomationBundle\Entity\EventConfig;

/**
 * 事件服务
 *
 * 负责:
 * 1. 计算事件的下次触发时间
 * 2. 判断事件是否满足触发条件
 */
class EventService
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * 计算事件的下次触发时间
     *
     * 根据事件配置的 Cron 表达式计算下次触发时间
     * 如果事件没有配置 Cron 表达式,返回 null
     *
     * @param EventConfig $eventConfig 事件配置
     * @return \DateTimeImmutable|null 下次触发时间,如果没有配置 Cron 表达式则返回 null
     *
     * @example
     * // 每天凌晨1点执行
     * $config->setCronExpression('0 1 * * *');
     * $service->calculateNextTriggerTime($config); // 返回下一个凌晨1点的时间
     */
    public function calculateNextTriggerTime(EventConfig $eventConfig): ?\DateTimeImmutable
    {
        if (!$eventConfig->getCronExpression()) {
            return null;
        }

        $cron = new CronExpression($eventConfig->getCronExpression());
        return \DateTimeImmutable::createFromMutable($cron->getNextRunDate());
    }

    /**
     * 判断事件是否满足触发条件
     *
     * 执行事件配置的触发条件 SQL,如果返回值大于0则表示满足触发条件
     * 如果事件没有配置触发条件 SQL,默认返回 true
     *
     * @param EventConfig $eventConfig 事件配置
     * @return bool 是否满足触发条件
     *
     * @example
     * // 检查是否有待处理的订单
     * $config->setTriggerSql('SELECT COUNT(*) FROM orders WHERE status = "pending"');
     * $service->shouldTrigger($config); // 如果有待处理订单返回 true
     */
    public function shouldTrigger(EventConfig $eventConfig): bool
    {
        if (!$eventConfig->getTriggerSql()) {
            return true;
        }

        try {
            $result = $this->connection->fetchOne($eventConfig->getTriggerSql());
            return $result > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}

<?php

namespace EventAutomationBundle\Event;

use EventAutomationBundle\Entity\EventConfig;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * 自动化事件
 *
 * 用于携带事件配置和上下文数据
 */
class AutomationEvent extends Event
{
    /**
     * @param EventConfig          $config  事件配置
     * @param array<string, mixed> $context 上下文数据,key 是变量名,value 是具体的数据
     */
    public function __construct(
        private readonly EventConfig $config,
        private readonly array $context = [],
    ) {
    }

    public function getConfig(): EventConfig
    {
        return $this->config;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * 获取事件名称
     *
     * 用于 EventDispatcher 分发事件
     */
    public function getName(): string
    {
        return $this->config->getIdentifier();
    }
}

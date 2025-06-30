<?php

declare(strict_types=1);

namespace EventAutomationBundle\Exception;

class EventProcessingException extends \RuntimeException
{
    /**
     * 创建事件处理异常
     * @param string $eventIdentifier 事件标识符
     * @param string $message 错误信息
     * @param \Throwable|null $previous 前一个异常
     * @return self
     */
    public static function forEvent(string $eventIdentifier, string $message, ?\Throwable $previous = null): self
    {
        return new self(
            sprintf('Event processing failed for "%s": %s', $eventIdentifier, $message),
            0,
            $previous
        );
    }
}
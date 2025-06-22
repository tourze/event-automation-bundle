<?php

namespace EventAutomationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

#[ORM\Entity]
#[ORM\Table(name: 'ims_event_automation_trigger_log', options: ['comment' => '事件触发日志'])]
class TriggerLog implements \Stringable
{
    use TimestampableAware;
    use CreatedByAware;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: EventConfig::class)]
    #[ORM\JoinColumn(nullable: false)]
    private EventConfig $eventConfig;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '触发时的上下文数据'])]
    private ?array $contextData = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '执行结果'])]
    private ?string $result = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventConfig(): EventConfig
    {
        return $this->eventConfig;
    }

    public function setEventConfig(EventConfig $eventConfig): self
    {
        $this->eventConfig = $eventConfig;
        return $this;
    }

    public function getContextData(): ?array
    {
        return $this->contextData;
    }

    public function setContextData(?array $contextData): self
    {
        $this->contextData = $contextData;
        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('TriggerLog #%s for %s', $this->id, $this->eventConfig->getName());
    }
}

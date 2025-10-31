<?php

namespace EventAutomationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EventConfig::class)]
    #[ORM\JoinColumn(nullable: false)]
    private EventConfig $eventConfig;

    /** @var array<string, mixed>|null */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '触发时的上下文数据'])]
    private ?array $contextData = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
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

    public function setEventConfig(EventConfig $eventConfig): void
    {
        $this->eventConfig = $eventConfig;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContextData(): ?array
    {
        return $this->contextData;
    }

    /**
     * @param array<string, mixed>|null $contextData
     */
    public function setContextData(?array $contextData): void
    {
        $this->contextData = $contextData;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): void
    {
        $this->result = $result;
    }

    public function __toString(): string
    {
        return sprintf('TriggerLog #%s for %s', $this->id, $this->eventConfig->getName());
    }
}

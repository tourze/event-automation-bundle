<?php

namespace EventAutomationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity]
#[ORM\Table(name: 'ims_event_automation_context', options: ['comment' => '事件上下文配置'])]
class ContextConfig implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: EventConfig::class)]
    #[ORM\JoinColumn(nullable: false)]
    private EventConfig $eventConfig;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '上下文变量名'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体类名'])]
    private string $entityClass;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '查询SQL'])]
    private string $querySql;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '查询参数配置'])]
    private ?array $queryParams = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    public function getQuerySql(): string
    {
        return $this->querySql;
    }

    public function setQuerySql(string $querySql): self
    {
        $this->querySql = $querySql;
        return $this;
    }

    public function getQueryParams(): ?array
    {
        return $this->queryParams;
    }

    public function setQueryParams(?array $queryParams): self
    {
        $this->queryParams = $queryParams;
        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

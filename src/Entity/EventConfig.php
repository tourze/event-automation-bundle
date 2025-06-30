<?php

namespace EventAutomationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EventAutomationBundle\Repository\EventConfigRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: EventConfigRepository::class)]
#[ORM\Table(name: 'ims_event_automation_config', options: ['comment' => '事件自动化配置'])]
class EventConfig implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '事件名称'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '事件标识符'])]
    private string $identifier;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'Cron 表达式,用于定时触发'])]
    private ?string $cronExpression = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '触发条件SQL'])]
    private ?string $triggerSql = null;

    #[ORM\OneToMany(targetEntity: ContextConfig::class, mappedBy: 'eventConfig', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contextConfigs;

    #[ORM\OneToMany(targetEntity: TriggerLog::class, mappedBy: 'eventConfig')]
    private Collection $triggerLogs;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->contextConfigs = new ArrayCollection();
        $this->triggerLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    public function setCronExpression(?string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;
        return $this;
    }

    public function getTriggerSql(): ?string
    {
        return $this->triggerSql;
    }

    public function setTriggerSql(?string $triggerSql): self
    {
        $this->triggerSql = $triggerSql;
        return $this;
    }

    /**
     * @return Collection<int, ContextConfig>
     */
    public function getContextConfigs(): Collection
    {
        return $this->contextConfigs;
    }

    public function addContextConfig(ContextConfig $contextConfig): self
    {
        if (!$this->contextConfigs->contains($contextConfig)) {
            $this->contextConfigs->add($contextConfig);
            $contextConfig->setEventConfig($this);
        }

        return $this;
    }

    public function removeContextConfig(ContextConfig $contextConfig): self
    {
        $this->contextConfigs->removeElement($contextConfig);

        return $this;
    }

    /**
     * @return Collection<int, TriggerLog>
     */
    public function getTriggerLogs(): Collection
    {
        return $this->triggerLogs;
    }

    public function getLastTriggerLog(): ?TriggerLog
    {
        $lastLog = $this->triggerLogs->last();
        return $lastLog !== false ? $lastLog : null;
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

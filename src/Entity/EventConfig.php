<?php

namespace EventAutomationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EventAutomationBundle\Repository\EventConfigRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;

#[ORM\Entity(repositoryClass: EventConfigRepository::class)]
#[ORM\Table(name: 'ims_event_automation_config')]
class EventConfig
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => '事件名称'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => '事件标识符'])]
    private string $identifier;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['comment' => 'Cron 表达式,用于定时触发'])]
    private ?string $cronExpression = null;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => '触发条件SQL'])]
    private ?string $triggerSql = null;

    #[ORM\OneToMany(targetEntity: ContextConfig::class, mappedBy: 'eventConfig', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contextConfigs;

    #[ORM\OneToMany(targetEntity: TriggerLog::class, mappedBy: 'eventConfig')]
    private Collection $triggerLogs;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

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
        if ($this->contextConfigs->removeElement($contextConfig)) {
            if ($contextConfig->getEventConfig() === $this) {
                $contextConfig->setEventConfig(null);
            }
        }

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
        return $this->triggerLogs->last() ?: null;
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

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }
}

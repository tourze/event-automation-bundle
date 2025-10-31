<?php

namespace EventAutomationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EventConfig::class)]
    #[ORM\JoinColumn(nullable: false)]
    private EventConfig $eventConfig;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '上下文变量名'])]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体类名'])]
    private string $entityClass;

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '查询SQL'])]
    private string $querySql;

    /** @var array<string, mixed>|null */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '查询参数配置'])]
    private ?array $queryParams = null;

    #[Assert\Type(type: 'bool')]
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

    public function setEventConfig(EventConfig $eventConfig): void
    {
        $this->eventConfig = $eventConfig;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    public function getQuerySql(): string
    {
        return $this->querySql;
    }

    public function setQuerySql(string $querySql): void
    {
        $this->querySql = $querySql;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getQueryParams(): ?array
    {
        return $this->queryParams;
    }

    /**
     * @param array<string, mixed>|null $queryParams
     */
    public function setQueryParams(?array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

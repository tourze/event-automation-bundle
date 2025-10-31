<?php

declare(strict_types=1);

namespace EventAutomationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EventAutomationBundle\Entity\TriggerLog;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<TriggerLog>
 * @method TriggerLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TriggerLog|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method TriggerLog[]    findAll()
 * @method TriggerLog[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
#[AsRepository(entityClass: TriggerLog::class)]
class TriggerLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TriggerLog::class);
    }

    public function save(TriggerLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TriggerLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

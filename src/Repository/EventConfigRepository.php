<?php

namespace EventAutomationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EventAutomationBundle\Entity\EventConfig;

/**
 * @method EventConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventConfig[] findAll()
 * @method EventConfig[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventConfig::class);
    }
}

<?php

namespace EventAutomationBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;

class ContextConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_CONTEXT_REFERENCE = 'user-context';
    public const ORDER_CONTEXT_REFERENCE = 'order-context';

    public function load(ObjectManager $manager): void
    {
        $contextConfig = new ContextConfig();
        $contextConfig->setName('user');
        $contextConfig->setEntityClass('App\Entity\User');
        $contextConfig->setQuerySql('SELECT * FROM users WHERE id = :user_id');
        $contextConfig->setQueryParams(['user_id' => null]);
        $contextConfig->setValid(true);
        $contextConfig->setEventConfig($this->getReference(EventConfigFixtures::USER_REGISTRATION_REFERENCE, EventConfig::class));

        $manager->persist($contextConfig);

        $contextConfig2 = new ContextConfig();
        $contextConfig2->setName('order');
        $contextConfig2->setEntityClass('App\Entity\Order');
        $contextConfig2->setQuerySql('SELECT * FROM orders WHERE user_id = :user_id AND status = :status');
        $contextConfig2->setQueryParams(['user_id' => null, 'status' => 'pending']);
        $contextConfig2->setValid(true);
        $contextConfig2->setEventConfig($this->getReference(EventConfigFixtures::ORDER_STATUS_REFERENCE, EventConfig::class));

        $manager->persist($contextConfig2);
        $manager->flush();

        $this->addReference(self::USER_CONTEXT_REFERENCE, $contextConfig);
        $this->addReference(self::ORDER_CONTEXT_REFERENCE, $contextConfig2);
    }

    public function getDependencies(): array
    {
        return [
            EventConfigFixtures::class,
        ];
    }
}

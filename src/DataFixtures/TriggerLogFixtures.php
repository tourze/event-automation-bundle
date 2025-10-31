<?php

namespace EventAutomationBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Entity\TriggerLog;

class TriggerLogFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_REGISTRATION_LOG_REFERENCE = 'user-registration-log';
    public const ORDER_STATUS_LOG_REFERENCE = 'order-status-log';
    public const PAYMENT_COMPLETED_LOG_REFERENCE = 'payment-completed-log';

    public function load(ObjectManager $manager): void
    {
        $triggerLog1 = new TriggerLog();
        $triggerLog1->setEventConfig($this->getReference(EventConfigFixtures::USER_REGISTRATION_REFERENCE, EventConfig::class));
        $triggerLog1->setContextData([
            'user_id' => 123,
            'email' => 'test@internal.local',
            'registration_time' => '2024-01-15 10:30:00',
        ]);
        $triggerLog1->setResult('Event processed successfully');

        $manager->persist($triggerLog1);

        $triggerLog2 = new TriggerLog();
        $triggerLog2->setEventConfig($this->getReference(EventConfigFixtures::ORDER_STATUS_REFERENCE, EventConfig::class));
        $triggerLog2->setContextData([
            'order_id' => 456,
            'user_id' => 123,
            'old_status' => 'pending',
            'new_status' => 'completed',
        ]);
        $triggerLog2->setResult('Order notification sent');

        $manager->persist($triggerLog2);

        $triggerLog3 = new TriggerLog();
        $triggerLog3->setEventConfig($this->getReference(EventConfigFixtures::PAYMENT_COMPLETED_REFERENCE, EventConfig::class));
        $triggerLog3->setContextData([
            'payment_id' => 789,
            'amount' => 99.99,
            'currency' => 'USD',
        ]);
        $triggerLog3->setResult('Payment confirmation email sent');

        $manager->persist($triggerLog3);
        $manager->flush();

        $this->addReference(self::USER_REGISTRATION_LOG_REFERENCE, $triggerLog1);
        $this->addReference(self::ORDER_STATUS_LOG_REFERENCE, $triggerLog2);
        $this->addReference(self::PAYMENT_COMPLETED_LOG_REFERENCE, $triggerLog3);
    }

    public function getDependencies(): array
    {
        return [
            EventConfigFixtures::class,
        ];
    }
}

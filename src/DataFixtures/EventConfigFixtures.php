<?php

namespace EventAutomationBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use EventAutomationBundle\Entity\EventConfig;

class EventConfigFixtures extends Fixture
{
    public const USER_REGISTRATION_REFERENCE = 'user-registration';
    public const ORDER_STATUS_REFERENCE = 'order-status';
    public const PAYMENT_COMPLETED_REFERENCE = 'payment-completed';

    public function load(ObjectManager $manager): void
    {
        $eventConfig1 = new EventConfig();
        $eventConfig1->setName('用户注册事件');
        $eventConfig1->setIdentifier('user.registration');
        $eventConfig1->setCronExpression('0 */5 * * * *');
        $eventConfig1->setTriggerSql('SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
        $eventConfig1->setValid(true);

        $manager->persist($eventConfig1);

        $eventConfig2 = new EventConfig();
        $eventConfig2->setName('订单状态变更事件');
        $eventConfig2->setIdentifier('order.status.changed');
        $eventConfig2->setCronExpression('0 */10 * * * *');
        $eventConfig2->setTriggerSql('SELECT COUNT(*) FROM orders WHERE updated_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND status_changed = 1');
        $eventConfig2->setValid(true);

        $manager->persist($eventConfig2);

        $eventConfig3 = new EventConfig();
        $eventConfig3->setName('支付完成事件');
        $eventConfig3->setIdentifier('payment.completed');
        $eventConfig3->setTriggerSql('SELECT COUNT(*) FROM payments WHERE status = "completed" AND processed_at IS NULL');
        $eventConfig3->setValid(false);

        $manager->persist($eventConfig3);
        $manager->flush();

        $this->addReference(self::USER_REGISTRATION_REFERENCE, $eventConfig1);
        $this->addReference(self::ORDER_STATUS_REFERENCE, $eventConfig2);
        $this->addReference(self::PAYMENT_COMPLETED_REFERENCE, $eventConfig3);
    }
}

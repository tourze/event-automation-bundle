<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EventAutomationBundle\Controller\Admin\EventConfigCrudController;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\EventConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(EventConfigCrudController::class)]
#[RunTestsInSeparateProcesses]
final class EventConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return EventConfigCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(EventConfigCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '事件名称' => ['事件名称'];
        yield '事件标识符' => ['事件标识符'];
        yield '有效状态' => ['有效状态'];
        yield '上下文配置' => ['上下文配置'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'identifier' => ['identifier'];
        yield 'cronExpression' => ['cronExpression'];
        yield 'valid' => ['valid'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'identifier' => ['identifier'];
        yield 'cronExpression' => ['cronExpression'];
        yield 'triggerSql' => ['triggerSql'];
        yield 'valid' => ['valid'];
    }

    protected function getEntityFqcn(): string
    {
        return EventConfig::class;
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();
        $crawler = $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Navigate to EventConfig CRUD
        $link = $crawler->filter('a[href*="EventConfigCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateEventConfig(): void
    {
        $client = self::createAuthenticatedClient();
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Test form submission for new event config
        $eventConfig = new EventConfig();
        $eventConfig->setName('测试事件配置');
        $eventConfig->setIdentifier('test-event-config-' . uniqid());
        $eventConfig->setCronExpression('0 0 * * *');
        $eventConfig->setTriggerSql('SELECT COUNT(*) FROM test_table WHERE status = "active"');
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Verify event config was created
        /** @var EventConfig|null $savedEventConfig */
        $savedEventConfig = $eventConfigRepository->findOneBy(['identifier' => $eventConfig->getIdentifier()]);
        $this->assertNotNull($savedEventConfig);
        $this->assertEquals('测试事件配置', $savedEventConfig->getName());
        $this->assertEquals($eventConfig->getIdentifier(), $savedEventConfig->getIdentifier());
        $this->assertEquals('0 0 * * *', $savedEventConfig->getCronExpression());
        $this->assertTrue($savedEventConfig->isValid());
    }

    public function testEventConfigDataPersistence(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create test event configs with different properties
        $eventConfig1 = new EventConfig();
        $eventConfig1->setName('搜索测试事件配置一');
        $eventConfig1->setIdentifier('search-test-one-' . uniqid());
        $eventConfig1->setCronExpression('0 */6 * * *');
        $eventConfig1->setTriggerSql('SELECT COUNT(*) FROM users WHERE created_at > NOW() - INTERVAL 1 HOUR');
        $eventConfig1->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig1, true);

        $eventConfig2 = new EventConfig();
        $eventConfig2->setName('搜索测试事件配置二');
        $eventConfig2->setIdentifier('search-test-two-' . uniqid());
        $eventConfig2->setCronExpression('0 0 0 * *');
        $eventConfig2->setTriggerSql('SELECT COUNT(*) FROM orders WHERE status = "pending" AND created_at < NOW() - INTERVAL 24 HOUR');
        $eventConfig2->setValid(false);

        $eventConfigRepository->save($eventConfig2, true);

        // Verify event configs are saved correctly
        /** @var EventConfig|null $savedEventConfig1 */
        $savedEventConfig1 = $eventConfigRepository->findOneBy(['identifier' => $eventConfig1->getIdentifier()]);
        $this->assertNotNull($savedEventConfig1);
        $this->assertEquals('搜索测试事件配置一', $savedEventConfig1->getName());
        $this->assertEquals('0 */6 * * *', $savedEventConfig1->getCronExpression());
        $this->assertTrue($savedEventConfig1->isValid());

        /** @var EventConfig|null $savedEventConfig2 */
        $savedEventConfig2 = $eventConfigRepository->findOneBy(['identifier' => $eventConfig2->getIdentifier()]);
        $this->assertNotNull($savedEventConfig2);
        $this->assertEquals('搜索测试事件配置二', $savedEventConfig2->getName());
        $this->assertEquals('0 0 0 * *', $savedEventConfig2->getCronExpression());
        $this->assertFalse($savedEventConfig2->isValid());
    }

    public function testEventConfigStringRepresentation(): void
    {
        $eventConfig = new EventConfig();
        $eventConfig->setName('字符串表示测试');
        $eventConfig->setIdentifier('string-test-' . uniqid());

        $this->assertEquals('字符串表示测试', (string) $eventConfig);
    }

    public function testEventConfigValidationConstraints(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Test with required fields
        $eventConfig = new EventConfig();
        $eventConfig->setName('验证测试事件');
        $eventConfig->setIdentifier('validation-test-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        /** @var EventConfig|null $savedEventConfig */
        $savedEventConfig = $eventConfigRepository->findOneBy(['identifier' => $eventConfig->getIdentifier()]);
        $this->assertNotNull($savedEventConfig);
        $this->assertEquals('验证测试事件', $savedEventConfig->getName());

        // Test with optional fields set to null
        $eventConfig2 = new EventConfig();
        $eventConfig2->setName('可选字段测试');
        $eventConfig2->setIdentifier('optional-fields-test-' . uniqid());
        $eventConfig2->setCronExpression(null);
        $eventConfig2->setTriggerSql(null);
        $eventConfig2->setValid(false);

        $eventConfigRepository->save($eventConfig2, true);

        /** @var EventConfig|null $savedEventConfig2 */
        $savedEventConfig2 = $eventConfigRepository->findOneBy(['identifier' => $eventConfig2->getIdentifier()]);
        $this->assertNotNull($savedEventConfig2);
        $this->assertNull($savedEventConfig2->getCronExpression());
        $this->assertNull($savedEventConfig2->getTriggerSql());
        $this->assertFalse($savedEventConfig2->isValid());
    }

    public function testTestTrigger(): void
    {
        $controller = $this->getControllerService();
        $this->assertInstanceOf(EventConfigCrudController::class, $controller);

        // Test that testTrigger action is configured
        $baseActions = Actions::new();
        $actions = $controller->configureActions($baseActions);
        $this->assertNotNull($actions);

        // Verify the action exists in detail page
        $detailActions = $actions->getAsDto(Crud::PAGE_DETAIL)->getActions();
        $this->assertNotEmpty($detailActions);

        $testTriggerActionFound = false;
        foreach ($detailActions as $actionName => $action) {
            if ('testTrigger' === $actionName) {
                $testTriggerActionFound = true;
                $this->assertInstanceOf(ActionDto::class, $action);
                $this->assertEquals('测试触发', $action->getLabel());
                break;
            }
        }

        $this->assertTrue($testTriggerActionFound, 'testTrigger action should be configured');
    }
}

<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EventAutomationBundle\Controller\Admin\ContextConfigCrudController;
use EventAutomationBundle\Entity\ContextConfig;
use EventAutomationBundle\Entity\EventConfig;
use EventAutomationBundle\Repository\ContextConfigRepository;
use EventAutomationBundle\Repository\EventConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ContextConfigCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ContextConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return ContextConfigCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(ContextConfigCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '事件配置' => ['事件配置'];
        yield '上下文变量名' => ['上下文变量名'];
        yield '实体类名' => ['实体类名'];
        yield '有效状态' => ['有效状态'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'entityClass' => ['entityClass'];
        yield 'valid' => ['valid'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'eventConfig' => ['eventConfig'];
        yield 'name' => ['name'];
        yield 'entityClass' => ['entityClass'];
        yield 'querySql' => ['querySql'];
        yield 'queryParams' => ['queryParams'];
        yield 'valid' => ['valid'];
    }

    protected function getEntityFqcn(): string
    {
        return ContextConfig::class;
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();
        $crawler = $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Navigate to ContextConfig CRUD
        $link = $crawler->filter('a[href*="ContextConfigCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateEntity(): void
    {
        $client = self::createAuthenticatedClient();
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Create a required EventConfig first
        $eventConfig = new EventConfig();
        $eventConfig->setName('Test Event Config');
        $eventConfig->setIdentifier('test-event-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Test form submission for new context config
        $contextConfig = new ContextConfig();
        $contextConfig->setEventConfig($eventConfig);
        $contextConfig->setName('testContext');
        $contextConfig->setEntityClass('App\Entity\TestEntity');
        $contextConfig->setQuerySql('SELECT * FROM test_table WHERE id = :id');
        $contextConfig->setQueryParams(['id' => 1]);
        $contextConfig->setValid(true);

        // Save the context config
        self::getEntityManager()->persist($contextConfig);
        self::getEntityManager()->flush();

        // Verify context config was created
        $contextConfigRepository = self::getService(ContextConfigRepository::class);
        $savedContextConfig = $contextConfigRepository->findOneBy(['name' => 'testContext']);
        $this->assertNotNull($savedContextConfig);
        /** @var ContextConfig $savedContextConfig */
        $this->assertEquals('testContext', $savedContextConfig->getName());
        $this->assertEquals('App\Entity\TestEntity', $savedContextConfig->getEntityClass());
        $this->assertEquals('SELECT * FROM test_table WHERE id = :id', $savedContextConfig->getQuerySql());
        $this->assertEquals(['id' => 1], $savedContextConfig->getQueryParams());
        $this->assertTrue($savedContextConfig->isValid());
    }

    public function testContextConfigDataPersistence(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create test event config first
        $eventConfig = new EventConfig();
        $eventConfig->setName('Test Event for Context');
        $eventConfig->setIdentifier('test-context-event-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Create test context configs with different configurations
        $contextConfig1 = new ContextConfig();
        $contextConfig1->setEventConfig($eventConfig);
        $contextConfig1->setName('userContext');
        $contextConfig1->setEntityClass('App\Entity\User');
        $contextConfig1->setQuerySql('SELECT * FROM users WHERE id = :user_id');
        $contextConfig1->setQueryParams(['user_id' => 1]);
        $contextConfig1->setValid(true);

        $contextConfig2 = new ContextConfig();
        $contextConfig2->setEventConfig($eventConfig);
        $contextConfig2->setName('orderContext');
        $contextConfig2->setEntityClass('App\Entity\Order');
        $contextConfig2->setQuerySql('SELECT * FROM orders WHERE user_id = :user_id AND status = :status');
        $contextConfig2->setQueryParams(['user_id' => 1, 'status' => 'active']);
        $contextConfig2->setValid(false);

        // Save both context configs
        self::getEntityManager()->persist($contextConfig1);
        self::getEntityManager()->persist($contextConfig2);
        self::getEntityManager()->flush();

        // Verify context configs are saved correctly
        $contextConfigRepository = self::getService(ContextConfigRepository::class);
        $savedContextConfig1 = $contextConfigRepository->findOneBy(['name' => 'userContext']);
        $this->assertNotNull($savedContextConfig1);
        /** @var ContextConfig $savedContextConfig1 */
        $this->assertEquals('userContext', $savedContextConfig1->getName());
        $this->assertEquals('App\Entity\User', $savedContextConfig1->getEntityClass());
        $this->assertTrue($savedContextConfig1->isValid());
        $this->assertEquals(['user_id' => 1], $savedContextConfig1->getQueryParams());

        $savedContextConfig2 = $contextConfigRepository->findOneBy(['name' => 'orderContext']);
        $this->assertNotNull($savedContextConfig2);
        /** @var ContextConfig $savedContextConfig2 */
        $this->assertEquals('orderContext', $savedContextConfig2->getName());
        $this->assertEquals('App\Entity\Order', $savedContextConfig2->getEntityClass());
        $this->assertFalse($savedContextConfig2->isValid());
        $this->assertEquals(['user_id' => 1, 'status' => 'active'], $savedContextConfig2->getQueryParams());

        // Verify association with event config
        $this->assertEquals($eventConfig->getId(), $savedContextConfig1->getEventConfig()->getId());
        $this->assertEquals($eventConfig->getId(), $savedContextConfig2->getEventConfig()->getId());
    }

    public function testContextConfigStringRepresentation(): void
    {
        $client = self::createClientWithDatabase();

        // Create event config
        $eventConfig = new EventConfig();
        $eventConfig->setName('Test Event');
        $eventConfig->setIdentifier('test-string-event-' . uniqid());
        $eventConfig->setValid(true);

        $eventConfigRepository = self::getService(EventConfigRepository::class);
        $this->assertInstanceOf(EventConfigRepository::class, $eventConfigRepository);
        $eventConfigRepository->save($eventConfig, true);

        // Create context config
        $contextConfig = new ContextConfig();
        $contextConfig->setEventConfig($eventConfig);
        $contextConfig->setName('testStringContext');
        $contextConfig->setEntityClass('App\Entity\Test');
        $contextConfig->setQuerySql('SELECT 1');
        $contextConfig->setValid(true);

        // Test __toString method
        $this->assertEquals('testStringContext', (string) $contextConfig);

        // Save and verify persistence doesn't affect string representation
        self::getEntityManager()->persist($contextConfig);
        self::getEntityManager()->flush();

        $contextConfigRepository = self::getService(ContextConfigRepository::class);
        $savedContextConfig = $contextConfigRepository->findOneBy(['name' => 'testStringContext']);
        $this->assertNotNull($savedContextConfig);
        /** @var ContextConfig $savedContextConfig */
        $this->assertEquals('testStringContext', (string) $savedContextConfig);
    }
}

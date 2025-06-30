<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\DependencyInjection;

use EventAutomationBundle\Command\ProcessEventCommand;
use EventAutomationBundle\DependencyInjection\EventAutomationExtension;
use EventAutomationBundle\Repository\EventConfigRepository;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventAutomationExtensionTest extends TestCase
{
    private EventAutomationExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new EventAutomationExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证主要服务已经被加载
        $this->assertTrue(
            $this->container->hasDefinition(EventService::class),
            'EventService should be registered'
        );

        $this->assertTrue(
            $this->container->hasDefinition(EventConfigRepository::class),
            'EventConfigRepository should be registered'
        );

        $this->assertTrue(
            $this->container->hasDefinition(ProcessEventCommand::class),
            'ProcessEventCommand should be registered'
        );
    }

    public function testLoadWithEmptyConfig(): void
    {
        $configs = [[]];
        $this->extension->load($configs, $this->container);

        // 即使配置为空，服务也应该被加载
        $this->assertTrue($this->container->hasDefinition(EventService::class));
    }

    public function testServiceDefinitionsHaveAutoconfigure(): void
    {
        $this->extension->load([], $this->container);

        // 验证命令定义启用了 autoconfigure
        $commandDefinition = $this->container->getDefinition(ProcessEventCommand::class);
        $this->assertTrue(
            $commandDefinition->isAutoconfigured(),
            'ProcessEventCommand should have autoconfigure enabled'
        );
    }
}
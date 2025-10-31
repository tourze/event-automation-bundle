<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\DependencyInjection;

use EventAutomationBundle\Command\ProcessEventCommand;
use EventAutomationBundle\DependencyInjection\EventAutomationExtension;
use EventAutomationBundle\Service\EventService;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(EventAutomationExtension::class)]
final class EventAutomationExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private EventAutomationExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        // Extension类需要直接实例化进行测试，这是正常的测试模式
        // @phpstan-ignore-next-line
        $this->extension = new EventAutomationExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
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

<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests;

use EventAutomationBundle\EventAutomationBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(EventAutomationBundle::class)]
#[RunTestsInSeparateProcesses]
final class EventAutomationBundleTest extends AbstractBundleTestCase
{
}

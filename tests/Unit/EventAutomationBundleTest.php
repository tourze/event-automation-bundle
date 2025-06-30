<?php

declare(strict_types=1);

namespace EventAutomationBundle\Tests\Unit;

use EventAutomationBundle\EventAutomationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EventAutomationBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new EventAutomationBundle();
        
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
    
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new EventAutomationBundle();
        
        $this->assertInstanceOf(EventAutomationBundle::class, $bundle);
    }
}
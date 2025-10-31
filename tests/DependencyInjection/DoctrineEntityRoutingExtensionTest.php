<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityRoutingBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineEntityRoutingBundle\DependencyInjection\DoctrineEntityRoutingExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineEntityRoutingExtension::class)]
final class DoctrineEntityRoutingExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Extension 测试不需要特殊的设置
    }

    public function testExtensionAlias(): void
    {
        $extension = new DoctrineEntityRoutingExtension();
        $this->assertEquals('doctrine_entity_routing', $extension->getAlias());
    }
}

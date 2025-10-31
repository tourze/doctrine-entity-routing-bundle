<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DoctrineEntityRoutingBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    /** @var array<mixed> */
    private array $originalEnv;

    protected function onSetUp(): void
    {
        $this->originalEnv = $_ENV;

        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    protected function onTearDown(): void
    {
        $_ENV = $this->originalEnv;
    }

    public function testLoadWithEnvironmentVariable(): void
    {
        $_ENV['ENTITY_METADATA_ROUTES'] = 'enabled';

        $routes = $this->loader->load('test_resource', 'entity_route');

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertGreaterThanOrEqual(0, $routes->count());
    }

    public function testLoadWithoutEnvironmentVariable(): void
    {
        unset($_ENV['ENTITY_METADATA_ROUTES']);

        $routes = $this->loader->load('test_resource', 'entity_route');

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(0, $routes);
    }

    public function testSupportsWithEntityRouteType(): void
    {
        $this->assertTrue($this->loader->supports('test_resource', 'entity_route'));
    }

    public function testSupportsWithOtherType(): void
    {
        $this->assertFalse($this->loader->supports('test_resource', 'other_type'));
        $this->assertFalse($this->loader->supports('test_resource', null));
    }

    public function testSupportsWithEmptyResource(): void
    {
        $this->assertTrue($this->loader->supports('', 'entity_route'));
        $this->assertFalse($this->loader->supports('', 'other_type'));
    }

    public function testAutoloadWhenNotLoaded(): void
    {
        $routes = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertGreaterThanOrEqual(0, $routes->count());
    }

    public function testAutoloadWhenAlreadyLoaded(): void
    {
        // 第一次调用autoload方法
        $firstRoutes = $this->loader->autoload();
        $this->assertInstanceOf(RouteCollection::class, $firstRoutes);

        // 第二次调用autoload方法应该返回空集合
        $secondRoutes = $this->loader->autoload();
        $this->assertInstanceOf(RouteCollection::class, $secondRoutes);
        $this->assertCount(0, $secondRoutes);
    }

    public function testLoaderCanBeInstantiated(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testLoadWithDifferentResourceNames(): void
    {
        $_ENV['ENTITY_METADATA_ROUTES'] = 'enabled';

        $routes1 = $this->loader->load('resource1', 'entity_route');
        $routes2 = $this->loader->load('resource2', 'entity_route');

        $this->assertInstanceOf(RouteCollection::class, $routes1);
        $this->assertInstanceOf(RouteCollection::class, $routes2);
    }
}

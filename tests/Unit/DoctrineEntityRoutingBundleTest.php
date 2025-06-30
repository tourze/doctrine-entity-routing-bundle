<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\DoctrineEntityRoutingBundle\DependencyInjection\DoctrineEntityRoutingExtension;
use Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle;

class DoctrineEntityRoutingBundleTest extends TestCase
{
    private DoctrineEntityRoutingBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new DoctrineEntityRoutingBundle();
    }

    public function testBundleExtendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testGetContainerExtension(): void
    {
        $extension = $this->bundle->getContainerExtension();
        
        $this->assertInstanceOf(DoctrineEntityRoutingExtension::class, $extension);
    }

    public function testGetName(): void
    {
        $name = $this->bundle->getName();
        
        $this->assertEquals('DoctrineEntityRoutingBundle', $name);
    }

    public function testGetPath(): void
    {
        $path = $this->bundle->getPath();
        
        $this->assertStringContainsString('doctrine-entity-routing-bundle', $path);
        $this->assertStringEndsWith('src', $path);
    }

    public function testBuild(): void
    {
        $container = new ContainerBuilder();
        
        // build 方法不应该抛出异常
        $this->bundle->build($container);
        
        // 验证容器仍然是有效的
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testBoot(): void
    {
        // boot 方法不应该抛出异常
        $this->bundle->boot();
        
        // Bundle 应该保持相同的状态
        $this->assertInstanceOf(DoctrineEntityRoutingBundle::class, $this->bundle);
    }

    public function testShutdown(): void
    {
        // shutdown 方法不应该抛出异常
        $this->bundle->shutdown();
        
        // Bundle 应该保持相同的状态
        $this->assertInstanceOf(DoctrineEntityRoutingBundle::class, $this->bundle);
    }

    public function testGetNamespace(): void
    {
        $namespace = $this->bundle->getNamespace();
        
        $this->assertEquals('Tourze\DoctrineEntityRoutingBundle', $namespace);
    }

    public function testBundleCanBeInstantiatedMultipleTimes(): void
    {
        $bundle1 = new DoctrineEntityRoutingBundle();
        $bundle2 = new DoctrineEntityRoutingBundle();
        
        $this->assertNotSame($bundle1, $bundle2);
        $this->assertEquals($bundle1->getName(), $bundle2->getName());
    }

    public function testBundleHasConsistentExtension(): void
    {
        $extension1 = $this->bundle->getContainerExtension();
        $extension2 = $this->bundle->getContainerExtension();
        
        // 应该返回相同的扩展实例
        $this->assertSame($extension1, $extension2);
    }
}
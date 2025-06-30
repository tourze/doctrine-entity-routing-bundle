<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineEntityRoutingBundle\DependencyInjection\DoctrineEntityRoutingExtension;

class DoctrineEntityRoutingExtensionTest extends TestCase
{
    private DoctrineEntityRoutingExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new DoctrineEntityRoutingExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadRegistersServices(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务是否被正确加载
        $this->assertTrue(
            $this->container->hasDefinition('tourze.doctrine_entity_routing.controller.entity_metadata'),
            'EntityMetadataController service should be registered'
        );
        
        $this->assertTrue(
            $this->container->hasDefinition('tourze.doctrine_entity_routing.attribute_controller_loader'),
            'AttributeControllerLoader service should be registered'
        );
    }

    public function testLoadWithEmptyConfiguration(): void
    {
        $configs = [];
        
        // 不应该抛出异常
        $this->extension->load($configs, $this->container);
        
        // 验证容器已经编译了一些定义
        $this->assertGreaterThan(0, count($this->container->getDefinitions()));
    }

    public function testLoadWithMultipleConfigurations(): void
    {
        $configs = [
            [],
            [],
        ];
        
        // 多次配置也不应该抛出异常
        $this->extension->load($configs, $this->container);
        
        // 验证服务仍然存在
        $this->assertTrue($this->container->hasDefinition('tourze.doctrine_entity_routing.controller.entity_metadata'));
    }

    public function testGetAlias(): void
    {
        // 验证扩展的别名
        $alias = $this->extension->getAlias();
        
        $this->assertEquals('doctrine_entity_routing', $alias);
    }

    public function testServicesArePublic(): void
    {
        $this->extension->load([], $this->container);
        
        // 获取控制器服务定义
        $this->assertTrue(
            $this->container->hasDefinition('tourze.doctrine_entity_routing.controller.entity_metadata'),
            'Controller service should be defined'
        );
        
        $controllerDefinition = $this->container->getDefinition('tourze.doctrine_entity_routing.controller.entity_metadata');
        
        // 控制器服务应该是公开的，以便可以被路由系统访问
        $this->assertTrue(
            $controllerDefinition->isPublic(),
            'Controller service should be public'
        );
    }

    public function testServicesHaveCorrectTags(): void
    {
        $this->extension->load([], $this->container);
        
        // 检查控制器是否有正确的标签
        $this->assertTrue(
            $this->container->hasDefinition('tourze.doctrine_entity_routing.controller.entity_metadata'),
            'Controller service should be defined'
        );
        
        $controllerDefinition = $this->container->getDefinition('tourze.doctrine_entity_routing.controller.entity_metadata');
        
        // 检查是否有 controller.service_arguments 标签
        $tags = $controllerDefinition->getTags();
        $this->assertArrayHasKey('controller.service_arguments', $tags);
    }

    public function testLoadDoesNotOverrideExistingServices(): void
    {
        // 先注册一个自定义服务
        $this->container->register('custom.service', \stdClass::class);
        
        // 加载扩展
        $this->extension->load([], $this->container);
        
        // 验证自定义服务仍然存在
        $this->assertTrue($this->container->hasDefinition('custom.service'));
        $this->assertEquals(\stdClass::class, $this->container->getDefinition('custom.service')->getClass());
    }
}
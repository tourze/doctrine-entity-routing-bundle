<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DoctrineEntityRoutingBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private ClassMetadataFactory|MockObject $metadataFactory;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $this->entityManager->method('getMetadataFactory')
            ->willReturn($this->metadataFactory);

        // 保存原始的环境变量
        $this->originalEnv = $_ENV;
    }

    protected function tearDown(): void
    {
        // 恢复原始的环境变量
        $_ENV = $this->originalEnv;
    }

    public function testLoad_withEnvironmentVariable(): void
    {
        // 设置环境变量
        $_ENV['ENTITY_METADATA_ROUTES'] = 'enabled';

        // 创建元数据模拟对象
        $metadata1 = $this->createMock(ClassMetadata::class);
        $metadata1->method('getTableName')->willReturn('test_table_1');

        $metadata2 = $this->createMock(ClassMetadata::class);
        $metadata2->method('getTableName')->willReturn('test_table_2');

        // 设置getAllMetadata方法返回两个元数据
        $this->metadataFactory->method('getAllMetadata')
            ->willReturn([$metadata1, $metadata2]);

        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 调用load方法
        $routes = $loader->load('test_resource', 'entity_route');

        // 验证返回的路由集合
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(2, $routes);

        // 验证路由名称
        $this->assertTrue($routes->get('entity_desc_test_table_1') !== null);
        $this->assertTrue($routes->get('entity_desc_test_table_2') !== null);

        // 验证路由路径
        $route1 = $routes->get('entity_desc_test_table_1');
        $this->assertEquals('/entity/desc/test_table_1', $route1->getPath());

        $route2 = $routes->get('entity_desc_test_table_2');
        $this->assertEquals('/entity/desc/test_table_2', $route2->getPath());
    }

    public function testLoad_withoutEnvironmentVariable(): void
    {
        // 确保环境变量未设置
        unset($_ENV['ENTITY_METADATA_ROUTES']);

        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 调用load方法
        $routes = $loader->load('test_resource', 'entity_route');

        // 验证返回的路由集合为空
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(0, $routes);
    }

    public function testSupports_withEntityRouteType(): void
    {
        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 验证支持entity_route类型
        $this->assertTrue($loader->supports('test_resource', 'entity_route'));
    }

    public function testSupports_withOtherType(): void
    {
        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 验证不支持其他类型
        $this->assertFalse($loader->supports('test_resource', 'other_type'));
        $this->assertFalse($loader->supports('test_resource', null));
    }

    public function testAutoload_whenNotLoaded(): void
    {
        // 创建元数据模拟对象
        $metadata1 = $this->createMock(ClassMetadata::class);
        $metadata1->method('getTableName')->willReturn('test_table_1');

        $metadata2 = $this->createMock(ClassMetadata::class);
        $metadata2->method('getTableName')->willReturn('test_table_2');

        // 设置getAllMetadata方法返回两个元数据
        $this->metadataFactory->method('getAllMetadata')
            ->willReturn([$metadata1, $metadata2]);

        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 调用autoload方法
        $routes = $loader->autoload();

        // 验证返回的路由集合
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(2, $routes);

        // 验证路由名称
        $this->assertTrue($routes->get('entity_desc_test_table_1') !== null);
        $this->assertTrue($routes->get('entity_desc_test_table_2') !== null);
    }

    public function testAutoload_whenAlreadyLoaded(): void
    {
        // 创建元数据模拟对象
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getTableName')->willReturn('test_table');

        // 设置getAllMetadata方法
        $this->metadataFactory->method('getAllMetadata')
            ->willReturn([$metadata]);

        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 第一次调用autoload方法
        $firstRoutes = $loader->autoload();
        $this->assertCount(1, $firstRoutes);

        // 第二次调用autoload方法
        $secondRoutes = $loader->autoload();

        // 验证第二次返回的路由集合为空
        $this->assertInstanceOf(RouteCollection::class, $secondRoutes);
        $this->assertCount(0, $secondRoutes);
    }

    public function testAutoload_withEmptyMetadata(): void
    {
        // 设置getAllMetadata方法返回空数组
        $this->metadataFactory->method('getAllMetadata')
            ->willReturn([]);

        // 创建路由加载器实例
        $loader = new AttributeControllerLoader($this->entityManager);

        // 调用autoload方法
        $routes = $loader->autoload();

        // 验证返回的路由集合为空
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(0, $routes);
    }
}

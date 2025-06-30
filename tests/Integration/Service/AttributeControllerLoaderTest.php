<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Integration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DoctrineEntityRoutingBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends KernelTestCase
{
    private AttributeControllerLoader $loader;
    private EntityManagerInterface $entityManager;

    protected static function getKernelClass(): string
    {
        return \Tourze\DoctrineEntityRoutingBundle\Tests\Integration\IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();
        
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->loader = new AttributeControllerLoader($this->entityManager);
    }

    public function testLoadWithoutEnvironmentVariable(): void
    {
        // 保存原始环境变量
        $originalEnv = $_ENV['ENTITY_METADATA_ROUTES'] ?? null;
        unset($_ENV['ENTITY_METADATA_ROUTES']);
        
        try {
            $routes = $this->loader->load(null);
            
            $this->assertInstanceOf(RouteCollection::class, $routes);
            $this->assertEquals(0, $routes->count());
        } finally {
            // 恢复环境变量
            if ($originalEnv !== null) {
                $_ENV['ENTITY_METADATA_ROUTES'] = $originalEnv;
            }
        }
    }

    public function testLoadWithEnvironmentVariable(): void
    {
        // 设置环境变量
        $_ENV['ENTITY_METADATA_ROUTES'] = 'true';
        
        try {
            $routes = $this->loader->load(null);
            
            $this->assertInstanceOf(RouteCollection::class, $routes);
            // 应该至少有一些路由
            $this->assertGreaterThan(0, $routes->count());
        } finally {
            unset($_ENV['ENTITY_METADATA_ROUTES']);
        }
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->loader->supports(null, 'entity_route'));
        $this->assertFalse($this->loader->supports(null, 'yaml'));
        $this->assertFalse($this->loader->supports(null, null));
        $this->assertFalse($this->loader->supports(null, 'annotation'));
    }

    public function testAutoloadCreatesRoutes(): void
    {
        $routes = $this->loader->autoload();
        
        $this->assertInstanceOf(RouteCollection::class, $routes);
        
        // 验证是否创建了动态路由
        foreach ($routes->all() as $routeName => $route) {
            $this->assertInstanceOf(Route::class, $route);
            
            if (str_starts_with($routeName, 'entity_desc_')) {
                // 验证动态路由的结构
                $this->assertStringStartsWith('/entity/desc/', $route->getPath());
                $defaults = $route->getDefaults();
                $this->assertArrayHasKey('_controller', $defaults);
                $this->assertArrayHasKey('tableName', $defaults);
                $this->assertStringContainsString('EntityMetadataController', $defaults['_controller']);
                $this->assertStringContainsString('getEntityMetadata', $defaults['_controller']);
            }
        }
    }

    public function testAutoloadIsIdempotent(): void
    {
        // 第一次调用
        $routes1 = $this->loader->autoload();
        $count1 = $routes1->count();
        
        // 第二次调用
        $routes2 = $this->loader->autoload();
        $count2 = $routes2->count();
        
        // 由于 isLoaded 标志，第二次应该返回空集合
        $this->assertGreaterThan(0, $count1);
        $this->assertEquals(0, $count2);
    }

    public function testAutoloadHandlesMultipleEntityMetadata(): void
    {
        // 创建一个新的 loader 实例以确保干净的状态
        $loader = new AttributeControllerLoader($this->entityManager);
        
        $routes = $loader->autoload();
        
        // 获取所有实体元数据
        $metadataFactory = $this->entityManager->getMetadataFactory();
        $allMetadata = $metadataFactory->getAllMetadata();
        
        // 对于每个实体，应该有一个对应的路由
        foreach ($allMetadata as $metadata) {
            $tableName = $metadata->getTableName();
            $routeName = 'entity_desc_' . $tableName;
            
            // 检查路由是否存在
            $route = $routes->get($routeName);
            if ($route !== null) {
                $this->assertEquals('/entity/desc/' . $tableName, $route->getPath());
            }
        }
    }

    public function testLoadCallsAutoloadWhenEnvironmentVariableIsSet(): void
    {
        $_ENV['ENTITY_METADATA_ROUTES'] = 'true';
        
        try {
            $routes = $this->loader->load(null);
            
            // 应该包含 autoload 创建的路由
            $this->assertGreaterThan(0, $routes->count());
            
            // 验证包含预期的路由类型
            $hasEntityDescRoute = false;
            foreach ($routes->all() as $routeName => $route) {
                if (str_starts_with($routeName, 'entity_desc_')) {
                    $hasEntityDescRoute = true;
                    break;
                }
            }
            
            $this->assertTrue($hasEntityDescRoute, 'Should have at least one entity_desc_ route');
        } finally {
            unset($_ENV['ENTITY_METADATA_ROUTES']);
        }
    }

    public function testRouteDefaultsAreCorrectlySet(): void
    {
        $routes = $this->loader->autoload();
        
        foreach ($routes->all() as $routeName => $route) {
            if (str_starts_with($routeName, 'entity_desc_')) {
                $defaults = $route->getDefaults();
                
                // 验证 _controller 格式
                $this->assertMatchesRegularExpression(
                    '/^Tourze\\\\DoctrineEntityRoutingBundle\\\\Controller\\\\EntityMetadataController::getEntityMetadata$/',
                    $defaults['_controller']
                );
                
                // 验证 tableName 存在且不为空
                $this->assertNotEmpty($defaults['tableName']);
                $this->assertIsString($defaults['tableName']);
            }
        }
    }
}
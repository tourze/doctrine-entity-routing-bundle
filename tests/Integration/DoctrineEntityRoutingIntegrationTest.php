<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Tourze\DoctrineEntityRoutingBundle\Controller\EntityMetadataController;
use Tourze\DoctrineEntityRoutingBundle\Service\EntityRouteLoader;
use Tourze\DoctrineEntityRoutingBundle\Tests\Integration\Entity\TestEntity;

/**
 * 测试DoctrineEntityRoutingBundle的集成功能
 *
 * 注意: 运行此测试需要在全局项目中安装以下依赖:
 * - doctrine/doctrine-bundle
 * - symfony/framework-bundle
 */
class DoctrineEntityRoutingIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        // 检查依赖
        $this->checkDependencies();

        // 启动内核
        self::bootKernel();
        $container = static::getContainer();

        // 获取实体管理器
        $entityManager = $container->get('doctrine.orm.entity_manager');
        assert($entityManager instanceof EntityManagerInterface);

        // 创建/更新数据库模式
        $schemaTool = new SchemaTool($entityManager);
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);

        // 创建一个测试实体
        $entity = new TestEntity();
        $entity->setName('测试实体');
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    public function testServiceWiring(): void
    {
        $container = static::getContainer();

        // 测试能否获取 EntityRouteLoader 服务
        $entityRouteLoader = $container->get(EntityRouteLoader::class);
        $this->assertInstanceOf(EntityRouteLoader::class, $entityRouteLoader);

        // 测试能否获取 EntityMetadataController 服务
        $entityMetadataController = $container->get(EntityMetadataController::class);
        $this->assertInstanceOf(EntityMetadataController::class, $entityMetadataController);
    }

    public function testRouteGeneration(): void
    {
        // 设置环境变量
        $_ENV['ENTITY_METADATA_ROUTES'] = 'enabled';

        $container = static::getContainer();

        // 获取路由器
        $router = $container->get('router');
        $this->assertInstanceOf(Router::class, $router);

        // 获取路由集合
        $routeCollection = $router->getRouteCollection();
        $this->assertInstanceOf(RouteCollection::class, $routeCollection);

        // 获取实体管理器查询表名
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $metadata = $entityManager->getClassMetadata(TestEntity::class);
        $tableName = $metadata->getTableName();

        // 验证路由
        $routeName = 'entity_desc_' . $tableName;
        $route = $routeCollection->get($routeName);

        // 在某些情况下，路由可能需要通过手动加载来测试
        if ($route === null) {
            $entityRouteLoader = $container->get(EntityRouteLoader::class);
            $routes = $entityRouteLoader->autoload();
            $route = $routes->get($routeName);
        }

        // 断言路由存在并配置正确
        $this->assertNotNull($route, '路由未生成');
        $this->assertEquals('/entity/desc/' . $tableName, $route->getPath());

        // 验证控制器
        $defaults = $route->getDefaults();
        $this->assertArrayHasKey('_controller', $defaults);
        $this->assertStringContainsString('EntityMetadataController::getEntityMetadata', $defaults['_controller']);
        $this->assertArrayHasKey('tableName', $defaults);
        $this->assertEquals($tableName, $defaults['tableName']);
    }
}

<?php

namespace Tourze\DoctrineEntityRoutingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DoctrineEntityRoutingBundle\Controller\EntityMetadataController;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private bool $isLoaded = false;
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if (!isset($_ENV['ENTITY_METADATA_ROUTES'])) {
            return new RouteCollection();
        }

        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'entity_route' === $type;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        if ($this->isLoaded) {
            return $collection;
        }

        // 注册控制器
        $collection->addCollection($this->controllerLoader->load(EntityMetadataController::class));

        // 添加动态路由
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $meta) {
            $tableName = $meta->getTableName();
            $routePath = '/entity/desc/' . $tableName;

            $route = new Route(
                $routePath,
                [
                    '_controller' => EntityMetadataController::class . '::getEntityMetadata',
                    'tableName' => $tableName,
                ]
            );

            $collection->add('entity_desc_' . $tableName, $route);
        }

        $this->isLoaded = true;
        return $collection;
    }
}

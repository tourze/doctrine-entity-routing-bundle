<?php

namespace Tourze\DoctrineEntityRoutingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DoctrineEntityRoutingBundle\Controller\EntityMetadataController;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag('routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private bool $isLoaded = false;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
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
        $routes = new RouteCollection();
        if ($this->isLoaded) {
            return $routes;
        }

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

            $routes->add('entity_desc_' . $tableName, $route);
        }

        $this->isLoaded = true;
        return $routes;
    }
}

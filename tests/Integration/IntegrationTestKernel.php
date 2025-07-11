<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle;

class IntegrationTestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new DoctrineEntityRoutingBundle();
    }
    
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'test' => true,
                'secret' => 'test',
                'router' => [
                    'resource' => __DIR__ . '/routes.yaml',
                    'utf8' => true,
                ],
            ]);
            
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                    'auto_mapping' => true,
                    'mappings' => [
                        'DoctrineEntityRoutingBundle' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => __DIR__ . '/Entity',
                            'prefix' => 'Tourze\DoctrineEntityRoutingBundle\Tests\Integration\Entity',
                            'alias' => 'DoctrineEntityRoutingBundle',
                        ],
                    ],
                ],
            ]);
        });
    }
}

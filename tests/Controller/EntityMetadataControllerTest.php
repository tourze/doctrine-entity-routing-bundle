<?php

declare(strict_types=1);

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Controller;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\DoctrineEntityRoutingBundle\Controller\EntityMetadataController;
use Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

/**
 * @internal
 */
#[CoversClass(EntityMetadataController::class)]
#[RunTestsInSeparateProcesses]
final class EntityMetadataControllerTest extends AbstractWebTestCase
{
    /**
     * @return array<class-string, array<string, bool>>
     */
    public static function configureBundles(): array
    {
        return [
            FrameworkBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            DoctrineEntityRoutingBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
        ];
    }

    public function testInvokeWithNonexistentTable(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/entity/desc/nonexistent_table');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Table not found', $data['error']);
    }

    public function testInvokeWithEmptyTableName(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/entity/desc/');
    }

    public function testResponseIsJsonFormat(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/entity/desc/any_table');

        $response = $client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testUnauthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/entity/desc/test_table');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $client = self::createClientWithDatabase();
        $client->request($method, '/entity/desc/test_table');
    }
}

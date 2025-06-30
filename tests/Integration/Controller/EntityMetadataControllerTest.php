<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EntityMetadataControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return \Tourze\DoctrineEntityRoutingBundle\Tests\Integration\IntegrationTestKernel::class;
    }

    public function testGetEntityMetadataEndpoint(): void
    {
        $client = static::createClient();
        
        // 测试获取测试实体的元数据
        $client->request('GET', '/entity-metadata/test_entity');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('table', $content);
        $this->assertArrayHasKey('columns', $content);
        $this->assertEquals('test_entity', $content['table']);
        $this->assertIsArray($content['columns']);
        $this->assertNotEmpty($content['columns']);
        
        // 验证字段结构
        foreach ($content['columns'] as $column) {
            $this->assertArrayHasKey('field', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('length', $column);
            $this->assertArrayHasKey('nullable', $column);
        }
    }

    public function testGetEntityMetadataForNonExistentTable(): void
    {
        $client = static::createClient();
        
        // 测试不存在的表
        $client->request('GET', '/entity-metadata/non_existent_table');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Table not found', $content['error']);
    }

    public function testGetEntityMetadataWithSpecialCharactersInTableName(): void
    {
        $client = static::createClient();
        
        // 测试包含特殊字符的表名
        $client->request('GET', '/entity-metadata/test%20entity');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetEntityMetadataResponseStructure(): void
    {
        $client = static::createClient();
        
        // 假设 test_entity 表存在
        $client->request('GET', '/entity-metadata/test_entity');
        
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        
        // 确保有响应
        $this->assertContains($statusCode, [Response::HTTP_OK, Response::HTTP_NOT_FOUND]);
        
        if ($statusCode === Response::HTTP_OK) {
            $content = json_decode($response->getContent(), true);
            
            // 验证响应的 JSON 结构
            $this->assertIsArray($content);
            $this->assertArrayHasKey('table', $content);
            $this->assertArrayHasKey('columns', $content);
            
            // 验证 columns 是一个数组
            $this->assertIsArray($content['columns']);
            
            // 如果有列，验证每个列的结构
            if (!empty($content['columns'])) {
                $firstColumn = $content['columns'][0];
                $this->assertIsArray($firstColumn);
                $this->assertArrayHasKey('field', $firstColumn);
                $this->assertArrayHasKey('type', $firstColumn);
                $this->assertArrayHasKey('length', $firstColumn);
                $this->assertArrayHasKey('nullable', $firstColumn);
                
                // 验证数据类型
                $this->assertIsString($firstColumn['field']);
                $this->assertIsString($firstColumn['type']);
                $this->assertIsBool($firstColumn['nullable']);
            }
        } else {
            // 如果是 404，验证错误消息
            $content = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $content);
            $this->assertEquals('Table not found', $content['error']);
        }
    }
}
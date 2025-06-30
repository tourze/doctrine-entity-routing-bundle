<?php

namespace Tourze\DoctrineEntityRoutingBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EntityMetadataController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getEntityMetadata(string $tableName): JsonResponse
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $entityData = [];

        foreach ($metadata as $meta) {
            try {
                if ($meta->getTableName() === $tableName) {
                    foreach ($meta->getFieldNames() as $field) {
                        $fieldMapping = $meta->getFieldMapping($field);
                        $entityData[] = [
                            'field' => $field,
                            'type' => $fieldMapping->type,
                            'length' => $fieldMapping->length ?? null,
                            'nullable' => $fieldMapping->nullable ?? false,
                        ];
                    }

                    return new JsonResponse([
                        'table' => $tableName,
                        'columns' => $entityData,
                    ]);
                }
            } catch (\Throwable $exception) {
                $this->logger->error('查找和返回表结构时发生错误', [
                    'exception' => $exception,
                ]);
                continue;
            }
        }

        return new JsonResponse(['error' => 'Table not found'], 404);
    }
}

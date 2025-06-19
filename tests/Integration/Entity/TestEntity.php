<?php

namespace Tourze\DoctrineEntityRoutingBundle\Tests\Integration\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'test_entity', options: ['comment' => '测试实体'])]
class TestEntity implements \Stringable
{
    #[Id]
    #[Column(type: Types::STRING, length: 36, options: ['comment' => '主键ID'])]
    private string $id;

    #[Column(type: Types::STRING, length: 255, options: ['comment' => '名称'])]
    private string $name;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->id = 'test_' . uniqid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    public function __toString(): string
    {
        return $this->name;
    }
}

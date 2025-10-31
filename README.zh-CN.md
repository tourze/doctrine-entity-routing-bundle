# Doctrine Entity Routing Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://codecov.io/gh/tourze/doctrine-entity-routing-bundle)

一个自动为 Doctrine 实体元数据检查生成 REST API 路由的 Symfony Bundle。

## 功能特性

- 自动发现应用程序中的所有 Doctrine 实体
- 为实体元数据检查生成 REST API 端点
- 提供包含表结构信息的 JSON 响应
- 支持基于环境变量的路由生成控制
- 包含全面的测试覆盖

## 安装

```bash
composer require tourze/doctrine-entity-routing-bundle
```

## 配置

### 注册 Bundle

在 `config/bundles.php` 中添加 Bundle：

```php
return [
    // ... 其他 bundles
    Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle::class => ['all' => true],
];
```

### 启用路由生成

设置环境变量以启用路由生成：

```bash
# .env
ENTITY_METADATA_ROUTES=enabled
```

### 添加路由加载器

在 `config/routes.yaml` 中添加路由加载器：

```yaml
entity_routes:
    resource: .
    type: entity_route
```

## 使用方法

配置完成后，Bundle 将自动为所有 Doctrine 实体生成路由。

### API 端点

对于每个实体表，Bundle 会生成：

```text
GET /entity/desc/{table_name}
```

### 响应示例

```json
{
  "table": "user",
  "columns": [
    {
      "field": "id",
      "type": "integer",
      "length": null,
      "nullable": false
    },
    {
      "field": "email",
      "type": "string",
      "length": 255,
      "nullable": false
    },
    {
      "field": "name",
      "type": "string",
      "length": 100,
      "nullable": true
    }
  ]
}
```

### 错误响应

如果表未找到：

```json
{
  "error": "Table not found"
}
```

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- Doctrine Bundle 2.13+

## 依赖关系

- `tourze/symfony-routing-auto-loader-bundle` - 用于自动路由加载
- 标准 Symfony 和 Doctrine 组件

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/doctrine-entity-routing-bundle/tests
```

运行 PHPStan 分析：

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/doctrine-entity-routing-bundle
```

## 工作原理

1. **路由发现**：`AttributeControllerLoader` 自动发现所有 Doctrine 实体
2. **路由生成**：为每个实体表生成一个 REST 端点
3. **控制器**：`EntityMetadataController` 处理请求并返回 JSON 元数据
4. **环境控制**：只有在设置了 `ENTITY_METADATA_ROUTES` 时才生成路由

## 架构

- **AttributeControllerLoader**：实现 `RoutingAutoLoaderInterface` 自动生成路由
- **EntityMetadataController**：处理 API 请求并返回 JSON 元数据
- **集成**：与 `tourze/symfony-routing-auto-loader-bundle` 协同工作实现无缝路由加载

## 贡献

详情请参阅 [CONTRIBUTING.md](CONTRIBUTING.md) 了解如何为该项目做出贡献。

## 更新日志

详情请参阅 [CHANGELOG.md](CHANGELOG.md) 了解最近的更新内容。

## 许可证

MIT 许可证。详情请参阅 [LICENSE](LICENSE) 文件。

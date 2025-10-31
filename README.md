# Doctrine Entity Routing Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-entity-routing-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/doctrine-entity-routing-bundle.svg?style=flat-square)](https://codecov.io/gh/tourze/doctrine-entity-routing-bundle)

A Symfony Bundle that automatically generates REST API routes for Doctrine entity metadata inspection.

## Features

- Automatically discovers all Doctrine entities in your application
- Generates REST API endpoints for entity metadata inspection
- Provides JSON responses with table structure information
- Supports environment-based route generation control
- Includes comprehensive test coverage

## Installation

```bash
composer require tourze/doctrine-entity-routing-bundle
```

## Configuration

### Register the Bundle

Add the bundle to your `config/bundles.php`:

```php
return [
    // ... other bundles
    Tourze\DoctrineEntityRoutingBundle\DoctrineEntityRoutingBundle::class => ['all' => true],
];
```

### Enable Route Generation

Set the environment variable to enable route generation:

```bash
# .env
ENTITY_METADATA_ROUTES=enabled
```

### Add Route Loader

Add the route loader to your `config/routes.yaml`:

```yaml
entity_routes:
    resource: .
    type: entity_route
```

## Usage

Once configured, the bundle will automatically generate routes for all your Doctrine entities.

### API Endpoints

For each entity table, the bundle generates:

```text
GET /entity/desc/{table_name}
```

### Example Response

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

### Error Response

If the table is not found:

```json
{
  "error": "Table not found"
}
```

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- Doctrine Bundle 2.13+

## Dependencies

- `tourze/symfony-routing-auto-loader-bundle` - For automatic route loading
- Standard Symfony and Doctrine components

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/doctrine-entity-routing-bundle/tests
```

Run PHPStan analysis:

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/doctrine-entity-routing-bundle
```

## How It Works

1. **Route Discovery**: The `AttributeControllerLoader` automatically discovers all Doctrine entities
2. **Route Generation**: For each entity table, it generates a REST endpoint
3. **Controller**: The `EntityMetadataController` handles requests and returns JSON metadata
4. **Environment Control**: Routes are only generated when `ENTITY_METADATA_ROUTES` is set

## Architecture

- **AttributeControllerLoader**: Implements `RoutingAutoLoaderInterface` to automatically generate routes
- **EntityMetadataController**: Handles API requests and returns JSON metadata
- **Integration**: Works with `tourze/symfony-routing-auto-loader-bundle` for seamless route loading

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for details on what has changed recently.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) file for more information.
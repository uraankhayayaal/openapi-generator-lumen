# Installation

```bash
composer require uraankhayayaal/openapi-generator-lumen
```

Register Service Provider in [bootstrap/app.php](bootstrap/app.php):
```php
$app->register(Uraankhayayaal\OpenapiGeneratorLumen\Providers\OpenApiGeneratorProvider::class);
```

Add openapi generator command it to your [app/Console/Kernel.php](app/Console/Kernel.php) file:
```php
protected $commands = [
    \Uraankhayayaal\OpenapiGeneratorLumen\Console\Commands\OpenApiGeneratorCommand::class,
];
```

# Usage

And just use:
```bash
php artisan openapi:generate
```

# Customize, own config

Create config file in [config/openapi-generator.php](config/openapi-generator.php) with content:
```php
<?php

return [
    'title' => 'API DOCUMENTATION',
    'version' => '0.0.1',
    'servers' => [
        'local' => [
            'host' => 'http://localhost:8000',
            'description' => 'Local API server',
        ],
        'prod' => [
            'host' => 'https://example.domain',
            'description' => 'Production API server',
        ],
    ],
    'auth_middleware' => 'auth',
    'export' => [
        'path' => './openapi.json',
        'format' => 'json',
    ],
];
```

And add the config in [bootstrap/app.php](bootstrap/app.php) for override package config values:
```php
$app->configure('openapi-generator');
```
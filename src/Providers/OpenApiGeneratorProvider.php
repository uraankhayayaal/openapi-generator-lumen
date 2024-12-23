<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Providers;

class OpenApiGeneratorProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $path = realpath(__DIR__.'/../../config/config.php');

        $this->mergeConfigFrom($path, 'openapi-generator');
    }
}

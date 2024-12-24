<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Tests;

use Tests\TestCase;

class OpenApiGeneratorCommandTest extends TestCase
{
    public function testOpenApiGeneratorCommandRun()
    {
        $code = $this->artisan('openapi:generate');

        $this->assertEquals(0, $code);
    }
}
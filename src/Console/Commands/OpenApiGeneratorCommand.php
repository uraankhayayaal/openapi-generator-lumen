<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Console\Commands;

use Uraankhayayaal\OpenapiGeneratorLumen\Services\OpenApiGeneratorService;
use Illuminate\Console\Command;

class OpenApiGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate openapi documentation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $service = new OpenApiGeneratorService();

        $service->buildApiGetwayDoc()->save();
    }
}

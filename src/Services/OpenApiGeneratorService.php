<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Services;

use Uraankhayayaal\OpenapiGeneratorLumen\Enums\OpenApiScalarTypesMapEnum;
use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestFormData;
use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestQueryData;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use OpenApi\Analysis;
use OpenApi\Attributes as OA;
use OpenApi\Context;
use ReflectionClass;
use ReflectionMethod;

final class OpenApiGeneratorService
{
    private const BASE_FORM_DATA_CLASS = BaseRequestFormData::class;

    private const BASE_QUERY_DATA_CLASS = BaseRequestQueryData::class;

    private const BASE_RESPONSE_CLASS = JsonResource::class;

    private Analysis $analysis;

    public function __construct()
    {
        $this->analysis = new Analysis([], new Context());
        $this->analysis->openapi = new OA\OpenApi();
        $this->analysis->openapi->info = new OA\Info(title: config('openapi-generator.title'), version: config('openapi-generator.version'));
    }

    public function buildApiGetwayDoc(): self
    {
        $this->analysis->openapi->servers = $this->buildServers();

        $componentSchemas = [];

        $this->analysis->openapi->paths = [];
        $this->analysis->openapi->tags = [];

        $this->analysis->openapi->paths = $this->buildPaths($componentSchemas);

        $this->analysis->openapi->components = new OA\Components(
            schemas: $componentSchemas,
            securitySchemes: [
                new OA\SecurityScheme(
                    securityScheme: 'bearerAuth',
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                ),
            ],
        );

        return $this;
    }

    public function save(): void
    {
        $this->analysis->openapi->saveAs(config('openapi-generator.export.path'), config('openapi-generator.export.format'));
    }

    /**
     * @return OA\Server[]
     */
    private function buildServers(): array
    {
        $servers = [];

        foreach (config('openapi-generator.servers') as $server) {
            $servers[] = new OA\Server(
                url: $server['host'],
                description: $server['description'],
            );
        }

        return $servers;
    }

    /**
     * @return OA\PathItem[]
     */
    private function buildPaths(&$componentSchemas): array
    {
        $paths = [];

        foreach (Route::getRoutes() as $route) {
            if ($this->isClosure($route['action']) || !isset($route['action']['as'])) { // skip callbacks from routs
                continue;
            }

            $paths[] = $this->buildRoute($route, $componentSchemas);
        }

        return $paths;
    }

    /**
     * @param array<string,mixed> $route
     */
    private function buildRoute(array $route, &$componentSchemas): OA\PathItem
    {
        $httpMethod = strtolower($route['method']);
        $httpMethodClass = 'OpenApi\\Attributes\\' . ucfirst($httpMethod);

        $path = new $httpMethodClass(
            security: $this->getSecurity($route['action']),
            tags: [$route['action']['as']],
            parameters: $this->buildParameters($route['action']),
            requestBody: $this->buildRequestBody($route['action']),
            responses: $this->buildResponses($route['action'], $componentSchemas),
        );

        return new OA\PathItem(
            ...[
                'path' => $route['uri'],
                $httpMethod => $path,
            ],
        );
    }

    /**
     * @param Closure[]|string[] $action
     *
     * @return OA\Parameter[]
     */
    private function buildParameters(array $action): array
    {
        $refMethod = $this->getReflectionMethod($action['uses']);

        $parameters = [];

        foreach ($refMethod->getParameters() as $param) { // this params places at path
            $typeName = (string) $param->getType();

            // Query params
            if (class_exists($typeName)) {
                $reflect = new ReflectionClass($typeName);
                if ($reflect->isSubclassOf(self::BASE_QUERY_DATA_CLASS)) {
                    $props = $reflect->getProperties();
                    foreach ($props as $prop) {
                        $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                        $type && $parameters[] = new OA\Parameter(
                            name: $prop->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                            in: 'query',
                            required: !$type->isNullable(),
                            example: $prop->isDefault() ? $prop->getDefaultValue() : null,
                            schema: $type->getSwaggerType(),
                        );
                    }
                }
            }

            // Path params
            $type = OpenApiScalarTypesMapEnum::tryFrom($typeName); // get only defined types for openapi
            $type && $parameters[] = new OA\Parameter(
                name: $param->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                in: 'path',
                required: !$param->isOptional(),
                example: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                schema: $type->getSwaggerType(),
            );
        }

        return $parameters;
    }

    /**
     * @param Closure[]|string[] $action
     *
     * @return null|OA\RequestBody
     */
    private function buildRequestBody(array $action): ?OA\RequestBody
    {
        $refMethod = $this->getReflectionMethod($action['uses']);

        $requestObject = [
            'required' => [],
            'properties' => [],
        ];

        foreach ($refMethod->getParameters() as $param) {
            $typeName = (string) $param->getType();
            if (class_exists($typeName)) {
                $reflect = new ReflectionClass($typeName);
                if ($reflect->isSubclassOf(self::BASE_FORM_DATA_CLASS)) {
                    $props = $reflect->getProperties();
                    foreach ($props as $prop) {
                        $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                        if ($type) {
                            !$type->isNullable() && $requestObject['required'][] = $prop->getName();
                            $requestObject['properties'][] = new OA\Property(
                                property: $prop->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                                type: $type->getPropertyType(),
                                format: $type->getPropertyFormat(),
                                nullable: $type->isNullable(),
                            );
                        }
                    }
                }
            }
        }

        if (empty($requestObject['properties'])) {
            return null;
        }

        return new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    required: $requestObject['required'],
                    properties: $requestObject['properties'],
                ),
            ),
        );
    }

    /**
     * @param array<array-key,mixed> $action
     * @param array<array-key,OA\Schema> $componentSchemas
     *
     * @return array<array-key,OA\Response>
     */
    private function buildResponses(array $action, array &$componentSchemas): array
    {
        if (!isset($action['uses'])) {
            return [];
        }

        $refMethod = $this->getReflectionMethod($action['uses']);

        $typeName = (string) $refMethod->getReturnType();

        if ($typeName === JsonResponse::class) {
            return [
                new OA\Response(response: 200, description: 'Success'),
                new OA\Response(response: 401, description: 'Not authorized'),
                new OA\Response(response: 403, description: 'Forbidden'),
            ];
        }

        $reflect = new ReflectionClass($typeName);

        $properties = [];

        if ($reflect->isSubclassOf(self::BASE_RESPONSE_CLASS)) {
            $props = $reflect->getProperties();
            foreach ($props as $prop) {
                $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                if ($type) {
                    $popertyAttributes = [
                        'property' => $prop->getName(),
                        'type' => $type->getPropertyType(),
                        'format' => $type->getPropertyFormat(),
                        'nullable' => $type->isNullable(),
                    ];
                    if ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY) {
                        $popertyAttributes['items'] = new OA\Items(anyOf: [
                            new OA\Schema(type: 'integer'),
                            new OA\Schema(type: 'string'),
                            // TODO: Add there object typing (array<array-key,SomeObject> || SomeObject)
                        ]);
                    }
                    $properties[] = new OA\Property(
                        ...$popertyAttributes,
                    );
                }
            }
        }

        $schemaName = $reflect->getShortName();

        $componentSchemas[] = new OA\Schema(
            schema: $schemaName,
            type: 'object',
            properties: $properties,
        );

        return [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: $properties ? new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'data',
                                type: 'object',
                                ref: "#/components/schemas/$schemaName",
                            ),
                        ],
                    )
                ) : null,
            ),
            new OA\Response(response: 401, description: 'Not authorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ];
    }

    /**
     * @param array<array-key,mixed> $action
     */
    private function isClosure(array $action): bool
    {
        return isset($action[0]) && $action[0] instanceof Closure;
    }

    private function getReflectionMethod(string $uses): ReflectionMethod
    {
        [$className, $methodName] = explode('@', $uses);

        return new ReflectionMethod($className, $methodName);
    }

    /**
     * @param array<array-key,mixed> $action
     *
     * @return array<mixed>
     */
    private function getSecurity(array $action): array
    {
        if (
            isset($action['middleware'])
            && in_array(config('openapi-generator.auth_middleware'), $action['middleware'], true)
        ) {
            return [['bearerAuth' => []]];
        }

        return [];
    }
}

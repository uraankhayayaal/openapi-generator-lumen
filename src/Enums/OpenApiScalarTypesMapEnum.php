<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Enums;

use OpenApi\Attributes as OA;

enum OpenApiScalarTypesMapEnum: string
{
    case INT = 'int';
    case FLOAT = 'float';
    case NUMERIC = 'numeric';
    case REAL = 'real';
    case BOOL = 'bool';
    case STRING = 'string';
    case ARRAY = 'array';
    case NULLABLE_INT = '?int';
    case NULLABLE_FLOAT = '?float';
    case NULLABLE_NUMERIC = '?numeric';
    case NULLABLE_REAL = '?real';
    case NULLABLE_BOOL = '?bool';
    case NULLABLE_STRING = '?string';
    case NULLABLE_ARRAY = '?array';

    public function getSwaggerType(): OA\Schema
    {
        return match ($this) {
            self::INT => new OA\Schema(type: 'integer', format: 'int64', nullable: false),
            self::FLOAT => new OA\Schema(type: 'number', format: 'float', nullable: false),
            self::NUMERIC => new OA\Schema(type: 'number', format: 'double', nullable: false),
            self::REAL => new OA\Schema(type: 'number', nullable: false),
            self::BOOL => new OA\Schema(type: 'boolean', nullable: false),
            self::STRING => new OA\Schema(type: 'string', nullable: false),
            self::ARRAY => new OA\Schema(type: 'array', nullable: false, items: new OA\Items(
                oneOf: [
                    new OA\Schema(type: 'integer'),
                    new OA\Schema(type: 'string'),
                ],
            )),
            self::NULLABLE_INT => new OA\Schema(type: 'integer', format: 'int64', nullable: true),
            self::NULLABLE_FLOAT => new OA\Schema(type: 'number', format: 'float', nullable: true),
            self::NULLABLE_NUMERIC => new OA\Schema(type: 'number', format: 'double', nullable: true),
            self::NULLABLE_REAL => new OA\Schema(type: 'number', nullable: true),
            self::NULLABLE_BOOL => new OA\Schema(type: 'boolean', nullable: true),
            self::NULLABLE_STRING => new OA\Schema(type: 'string', nullable: true),
            self::NULLABLE_ARRAY => new OA\Schema(type: 'array', items: new OA\Items(
                oneOf: [
                    new OA\Schema(type: 'integer'),
                    new OA\Schema(type: 'string'),
                ],
            ), nullable: true),
        };
    }

    public function getPropertyType(): string
    {
        return match ($this) {
            self::INT, self::NULLABLE_INT => 'integer',
            self::FLOAT, self::NULLABLE_FLOAT => 'number',
            self::NUMERIC, self::NULLABLE_NUMERIC => 'number',
            self::REAL, self::NULLABLE_REAL => 'number',
            self::BOOL, self::NULLABLE_BOOL => 'boolean',
            self::STRING, self::NULLABLE_STRING => 'string',
            self::ARRAY, self::NULLABLE_ARRAY => 'array',
        };
    }

    public function getPropertyFormat(): ?string
    {
        return match ($this) {
            self::INT, self::NULLABLE_INT => 'int64',
            self::FLOAT, self::NULLABLE_FLOAT => 'float',
            self::NUMERIC, self::NULLABLE_NUMERIC => 'double',
            default => null,
        };
    }

    public function isNullable(): bool
    {
        return match ($this) {
            self::INT, self::FLOAT, self::NUMERIC, self::REAL, self::BOOL, self::STRING, self::ARRAY => false,
            self::NULLABLE_INT, self::NULLABLE_FLOAT, self::NULLABLE_NUMERIC, self::NULLABLE_REAL, self::NULLABLE_BOOL, self::NULLABLE_STRING, self::NULLABLE_ARRAY => true,
        };
    }
}

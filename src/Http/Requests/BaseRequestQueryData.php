<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests;

use Illuminate\Http\Request;
use ReflectionProperty;

abstract class BaseRequestQueryData extends BaseRequestData
{
    protected function getPropValue(Request $request, ReflectionProperty $prop): string
    {
        return $request->get($prop->getName());
    }
}

<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests;

use Illuminate\Http\Request;
use ReflectionProperty;

abstract class BaseRequestFormData extends BaseRequestData
{
    protected function getPropValue(Request $request, ReflectionProperty $prop): mixed
    {
        return $request->input($prop->getName());
    }
}

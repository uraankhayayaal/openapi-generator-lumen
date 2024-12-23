<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseRequestData
{
    use ProvidesConvenienceMethods;

    public function __construct(
        Request $request
    ) {
        $this->init($request);
    }

    public function init(Request $request): void
    {
        $this->validate($request, $this->rules(), $this->messages());

        $refClass = new ReflectionClass($this);

        foreach ($refClass->getProperties() as $prop) {
            $inputPropValue = $this->getPropValue($request, $prop);
            if ($inputPropValue !== null) {
                $this->{$prop->getName()} = $inputPropValue;
            }
        }

        unset($refClass);
        unset($inputPropValue);
    }

    abstract protected function getPropValue(Request $request, ReflectionProperty $prop): mixed;

    abstract public function rules(): array;

    abstract public function messages(): array;
}

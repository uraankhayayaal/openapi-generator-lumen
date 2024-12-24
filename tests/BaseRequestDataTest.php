<?php

declare(strict_types=1);

namespace Uraankhayayaal\OpenapiGeneratorLumen\Tests;

use Illuminate\Http\Request;
use Tests\TestCase;
use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestFormData;
use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestQueryData;

class BaseRequestDataTest extends TestCase
{
    public function testInitForPostRequest()
    {
        $request = new Request(request: ['foo' => 33333], server: ['REQUEST_METHOD' => 'POST']);

        $testingRequest = new TestingFormData($request);

        $this->assertEquals(33333, $testingRequest->foo);  
    }

    public function testInitForGetRequest()
    {
        $request = new Request(query: ['bar' => 'some text']);

        $testingRequest = new TestingQueryParams($request);

        $this->assertEquals('some text', $testingRequest->bar);  
    }
}

class TestingFormData extends BaseRequestFormData
{
    public int $foo;

    public function rules(): array
    {
        return [
            'foo' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'foo.required' => 'Foo is required',
        ];
    }
}

class TestingQueryParams extends BaseRequestQueryData
{
    public string $bar;

    public function rules(): array
    {
        return [
            'bar' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'bar.required' => 'Bar is required',
        ];
    }
}
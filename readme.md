### Installation

1. Install the package via Composer:
```bash
composer require uraankhayayaal/openapi-generator-lumen
```
2. Register the service provider in `bootstrap/app.php`:
```php
$app->register(Uraankhayayaal\OpenapiGeneratorLumen\Providers\OpenApiGeneratorProvider::class);
```
3. Add the openapi generator command to your `app/Console/Kernel.php` file:
```php
protected $commands = [
    \Uraankhayayaal\OpenapiGeneratorLumen\Console\Commands\OpenApiGeneratorCommand::class,
];
```

### Usage

#### Requests
We have to define request body params and query params for automatically generating OpenAPI.

1. **Query params** — use a request extended from `Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestQueryData` to define Query params like the following example:
```php
<?php

namespace App\Http\Requests;

use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestQueryData;

final class ConfirmQuery extends BaseRequestQueryData
{
    public string $hash;

    public function rules(): array
    {
        return [
            'hash' => 'required|max:64',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
```
Use in controller like the following:
```php
use App\Http\Requests\ConfirmQuery;
...
public function confirm(ConfirmQuery $query): UserResource
{
    ...
    $hash = $query->hash;
    ...
}
```

2. **Body params** — extend your POST, PUT, PATCH, or HEAD request that has a body from `Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestFormData` like the following example:
```php
<?php

namespace App\Http\Requests;

use Uraankhayayaal\OpenapiGeneratorLumen\Http\Requests\BaseRequestFormData;

final class RegisterForm extends BaseRequestFormData
{
    public string $username;
    public string $email;
    public string $phone;
    public string $password;
    public bool $isAgreeMarketing;
    public bool $isAgreePolicy;

    public function rules(): array
    {
        return [
            'username' => 'required|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'password' => 'required',
            'isAgreeMarketing' => 'required|boolean',
            'isAgreePolicy' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
        ];
    }
}
```
Use in controller like the following:
```php
use App\Http\Requests\RegisterForm;
...
public function register(RegisterForm $registerForm): JsonResource
{
    ...
    $username = $registerForm->username;
    ...
}
```

#### Responses
Extend your responses from `Illuminate\Http\Resources\Json\JsonResource` like the following example:
```php
<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @param string[] $roles */
    public function __construct(
        public int $id,
        public int $status,
        public string $email,
        public string $phone,
        public array $roles,
        public int $createdAt,
        public int $updatedAt,
    ) {}

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,
            'roles' => $this->roles,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
```
Use the response in your controller like the following:
```php
use App\Http\Responses\UserResource;
...
public function register(): UserResource
{
    ...
}
```

#### Generate
And just use:
```bash
php artisan openapi:generate
```

### Customize, own config
Create a config file in `config/openapi-generator.php` with content:
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
And add the config in `bootstrap/app.php` for overriding package config values:
```php
$app->configure('openapi-generator');
```
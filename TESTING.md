# Testing Guide

## Test Stack

| Tool | Purpose |
|------|---------|
| **PHPUnit** | PHP testing framework |
| **Laravel Testbench** | Package testing utilities |
| **Mockery** | Mocking framework |
| **Inertia Test Assertions** | Inertia response assertions |
| **Laravel Breeze** | Authentication test helpers |

## Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test method
php artisan test --filter=test_user_can_create

# Run with stop on failure
php artisan test --stop-on-failure
```

## Test Structure

```
tests/
├── Feature/                    # Feature/integration tests
│   └── Hive/
│       ├── StudentControllerTest.php
│       ├── StaffControllerTest.php
│       ├── Finance/
│       │   ├── InvoiceControllerTest.php
│       │   └── PaymentControllerTest.php
│       └── ...
├── Unit/                       # Unit tests
│   └── Models/
│       ├── UserTest.php
│       ├── InvoiceTest.php
│       └── ...
└── TestCase.php                # Base test case
```

## Writing Tests

### Feature Test Example

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Hive;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'finance']);
    }

    /** @test */
    public function user_can_view_invoice_index(): void
    {
        $this->actingAs($this->user)
            ->get('/hive/finance/invoices')
            ->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Hive/Finance/Invoice/Index')
                ->has('invoices')
                ->has('filters')
            );
    }

    /** @test */
    public function user_can_create_invoice(): void
    {
        $invoiceData = Invoice::factory()->make()->toArray();

        $this->actingAs($this->user)
            ->post('/hive/finance/invoices', $invoiceData)
            ->assertRedirect('/hive/finance/invoices')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $invoiceData['invoice_number'],
        ]);
    }
}
```

### Model Unit Test Example

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invoice_belongs_to_user(): void
    {
        $invoice = Invoice::factory()->create();
        
        $this->assertInstanceOf(User::class, $invoice->user);
    }

    /** @test */
    public function invoice_has_status_constants(): void
    {
        $this->assertEquals('pending', Invoice::STATUS_PENDING);
        $this->assertEquals('paid', Invoice::STATUS_PAID);
        $this->assertEquals('cancelled', Invoice::STATUS_CANCELLED);
    }

    /** @test */
    public function invoice_is_overdue_when_past_due(): void
    {
        $invoice = Invoice::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        $this->assertTrue($invoice->isOverdue());
    }
}
```

### Policy Test Example

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function super_admin_can_do_anything(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        
        $invoice = Invoice::factory()->create();
        $policy = new InvoicePolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $invoice));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $invoice));
        $this->assertTrue($policy->delete($admin, $invoice));
    }

    /** @test */
    public function finance_can_view_invoices(): void
    {
        $finance = User::factory()->create();
        $finance->assignRole('finance');
        
        $invoice = Invoice::factory()->create();
        $policy = new InvoicePolicy();

        $this->assertTrue($policy->viewAny($finance));
        $this->assertTrue($policy->view($finance, $invoice));
        $this->assertFalse($policy->create($finance));
    }
}
```

## Test Factories

### User Factory

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'user_number' => fake()->unique()->numerify('S#########'),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'emergency_contact' => fake()->name(),
            'emergency_phone' => fake()->phoneNumber(),
            'remember_token' => fake()->sha256(),
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'staff',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super-admin',
        ]);
    }

    public function finance(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'finance',
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole($user->role ?? 'student');
        });
    }
}
```

## Inertia Assertions

```php
// Assert component
$response->assertInertis(fn (AssertableInertia $page) => $page
    ->component('Hive/Students/Index')
    ->has('students')
    ->has('filters')
    ->where('students.0.id', $student->id)
);

// Assert specific prop
$response->assertInertia(fn (AssertableInertia $page) => $page
    ->where('title', 'Students')
);

// Assert missing prop
$response->assertInertia(fn (AssertableInertia $page) => $page
    ->missing('error')
);
```

## Database Transactions

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase; // Rolls back after each test
    
    // Or use DatabaseTransactions for faster tests
    use Illuminate\Foundation\Testing\DatabaseTransactions;
}
```

## Mocking

```php
// Mock a service
$this->mock(SignatoryService::class, function ($mock) {
    $mock->shouldReceive('getSignatoryName')
        ->andReturn('John Doe');
});

// Mock a facade
use Illuminate\Support\Facades\Cache;

Cache::shouldReceive('remember')
    ->andReturn('cached data');

// Partial mock
$model = Invoice::factory()->make();
$model->shouldReceive('isOverdue')->andReturn(true);
```

## API Testing

```php
/** @test */
public function api_can_get_invoices(): void
{
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    Invoice::factory()->count(3)->create();

    $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/invoices')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'invoice_number', 'amount', 'status']
            ]
        ]);
}
```

## Coverage Requirements

| Component | Minimum Coverage |
|-----------|-----------------|
| Models | 80% |
| Controllers | 70% |
| Services | 90% |
| Policies | 90% |
| Overall | 75% |

## CI/CD

Tests run automatically on GitHub Actions:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: hbci
        ports:
          - 3306:3306
      
      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, curl, zip, gd, bcmath, intl, pdo_mysql, redis
          coverage: xdebug
      
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      
      - name: Install Node dependencies
        run: npm ci
      
      - name: Run tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: hbci
          DB_USERNAME: root
          DB_PASSWORD: root
          REDIS_HOST: 127.0.0.1
        run: php artisan test --coverage
```

## Test Best Practices

1. **One assertion per test** — Where possible, test one behavior per method
2. **Descriptive test names** — `test_user_can_view_invoice_index` not `test_invoice`
3. **Use factories** — Don't manually create all required fields
4. **Refresh database** — Use `RefreshDatabase` trait for clean state
5. **Mock external services** — Mock mail, payments, PDF generation
6. **Test authorization** — Verify policies are enforced
7. **Test edge cases** — Null values, empty arrays, max lengths
8. **Use data providers** — For similar test cases with different inputs

---
paths: e2e/**/*.ts
---

# E2E Test Data Management

Test data is managed via dedicated E2E API endpoints that are **only available in local/testing environments**.

## Architecture Overview

E2E tests create and clean up data through dedicated E2E API endpoints using **API clients** (`*E2EApi` classes):

```
Test                              Backend
┌──────────────────────┐         ┌─────────────────────────────────┐
│ Playwright           │         │  app/Http/Controllers/E2E/      │
│                      │ HTTP    │  ├── CustomerController         │
│ CustomerE2EApi       │────────►│  ├── UserController             │
│ UserE2EApi           │         │  ├── CustomerUserController     │
│ CustomerUserE2EApi   │◄────────│  └── FeatureConfigController    │
└──────────────────────┘         └─────────────────────────────────┘
```

**Benefits:**
- **Test isolation** - Each test creates its own data
- **Parallel execution** - Tests don't interfere with each other
- **Clean state** - No leftover data between runs
- **Type safety** - Uses auto-generated TypeScript types from Laravel Data classes
- **RESTful** - One resource per controller, independent operations

## Frontend API Client Structure

### Directory Layout

```
e2e/fixtures/
├── api/                         # E2E API clients (flat structure)
│   ├── BaseE2EApi.ts            # Shared HTTP patterns
│   ├── CustomerE2EApi.ts        # Customer resource
│   ├── UserE2EApi.ts            # User resource
│   ├── CustomerUserE2EApi.ts    # CustomerUser pivot resource
│   ├── FeatureConfigE2EApi.ts   # FeatureConfig resource
│   └── index.ts                 # Barrel export
├── core/
│   └── TestDataOrchestrator.ts  # High-level test setup
├── test-data.ts                 # Public API exports
└── base.fixture.ts              # Playwright fixture definitions
```

### BaseE2EApi (Shared Patterns)

All API clients extend `BaseE2EApi` for consistent HTTP handling:

```typescript
// e2e/fixtures/api/BaseE2EApi.ts
import { APIRequestContext } from '@playwright/test'

export class BaseE2EApi {
  constructor(
    protected readonly request: APIRequestContext,
    protected readonly baseURL: string
  ) {}

  protected async post<T>(path: string, data?: unknown): Promise<T> {
    const response = await this.request.post(`${this.baseURL}${path}`, {
      data,
      headers: { 'Content-Type': 'application/json' },
    })

    if (!response.ok()) {
      const text = await response.text()
      throw new Error(`POST ${path} failed: ${response.status()} - ${text}`)
    }

    return response.json() as Promise<T>
  }

  protected async delete(path: string): Promise<void> {
    const response = await this.request.delete(`${this.baseURL}${path}`)

    if (!response.ok()) {
      const text = await response.text()
      throw new Error(`DELETE ${path} failed: ${response.status()} - ${text}`)
    }
  }
}
```

### Resource-Specific API Client

Each resource has its own API client using **generated TypeScript types**:

```typescript
// e2e/fixtures/api/CustomerE2EApi.ts
import { BaseE2EApi } from './BaseE2EApi'
import type { App } from '@/types/generated'

type CreateCustomerResponse = App.Data.Controllers.E2E.CustomerController.CreateCustomerResponseData

export class CustomerE2EApi extends BaseE2EApi {
  async create(options?: { name?: string }): Promise<CreateCustomerResponse> {
    return this.post<CreateCustomerResponse>('/e2e/customers', {
      name: options?.name,
    })
  }

  async delete(customerId: number): Promise<void> {
    return super.delete(`/e2e/customers/${customerId}`)
  }
}
```

### TestDataOrchestrator (High-Level Setup)

For common test scenarios, the orchestrator provides convenience methods:

```typescript
// e2e/fixtures/core/TestDataOrchestrator.ts
import { APIRequestContext } from '@playwright/test'
import {
  CustomerE2EApi,
  UserE2EApi,
  CustomerUserE2EApi,
  FeatureConfigE2EApi,
} from '../api'

export class TestDataOrchestrator {
  public readonly customers: CustomerE2EApi
  public readonly users: UserE2EApi
  public readonly customerUsers: CustomerUserE2EApi
  public readonly featureConfig: FeatureConfigE2EApi

  constructor(request: APIRequestContext, baseURL: string) {
    this.customers = new CustomerE2EApi(request, baseURL)
    this.users = new UserE2EApi(request, baseURL)
    this.customerUsers = new CustomerUserE2EApi(request, baseURL)
    this.featureConfig = new FeatureConfigE2EApi(request, baseURL)
  }

  /**
   * Create complete test setup: Customer, User, and CustomerUser link.
   */
  async createAuthenticatedUser(options?: {
    customerName?: string
    userName?: string
    userGroupId?: number
  }) {
    // Create independent resources
    const customer = await this.customers.create({ name: options?.customerName })
    const user = await this.users.create({ name: options?.userName })

    // Link them via pivot
    const customerUser = await this.customerUsers.create({
      customerId: customer.id,
      userId: user.id,
      userGroupId: options?.userGroupId,
    })

    return { customer, user, customerUser }
  }
}
```

## Playwright Fixtures

### Available Fixtures

```typescript
// e2e/fixtures/base.fixture.ts
import { test as base } from '@playwright/test'
import { TestDataOrchestrator } from './core/TestDataOrchestrator'

export const test = base.extend<{
  testDataHelper: TestDataOrchestrator
  testData: { customer: CustomerResponse; user: UserResponse; customerUser: CustomerUserResponse }
  authenticatedPage: { page: Page; testData: TestData }
}>({
  // ... fixture implementations
})
```

### `authenticatedPage` (Recommended)

For most tests - provides logged-in page with test data:

```typescript
import { test, expect } from '../../fixtures/base.fixture'

test('user sees dashboard', async ({ authenticatedPage }) => {
  const { page, testData } = authenticatedPage

  // page is already logged in
  await expect(page.getByText(testData.customer.name)).toBeVisible()
})
```

### `testData` (No Login)

When you need data but want to handle login yourself:

```typescript
test('login flow', async ({ page, testData }) => {
  await page.goto('/login')
  await page.getByLabel(/email/i).fill(testData.user.email)
  await page.getByLabel(/password/i).fill(testData.user.password)
  await page.getByRole('button', { name: /log in/i }).click()

  await expect(page).toHaveURL(/.*\/dashboard/)
})
```

### `testDataHelper` (Manual Control)

For complex scenarios requiring fine-grained control:

```typescript
test('can link user to multiple customers', async ({ page, testDataHelper }) => {
  // Create resources independently
  const customer1 = await testDataHelper.customers.create({ name: 'Company A' })
  const customer2 = await testDataHelper.customers.create({ name: 'Company B' })
  const user = await testDataHelper.users.create({ name: 'Multi-tenant User' })

  // Link user to both customers
  await testDataHelper.customerUsers.create({
    customerId: customer1.id,
    userId: user.id,
  })
  await testDataHelper.customerUsers.create({
    customerId: customer2.id,
    userId: user.id,
  })

  // Test multi-tenant behavior...
})
```

## Backend Controller Structure

### Routes (`routes/e2e.php`)

```php
<?php

use App\Http\Controllers\E2E\CustomerController;
use App\Http\Controllers\E2E\UserController;
use App\Http\Controllers\E2E\CustomerUserController;
use App\Http\Controllers\E2E\FeatureConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('e2e')->group(function () {
    // Customers (standalone resource)
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

    // Users (standalone resource)
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // CustomerUsers (pivot resource - links Customer and User)
    Route::post('/customer-users', [CustomerUserController::class, 'store']);
    Route::delete('/customer-users/{customerUser}', [CustomerUserController::class, 'destroy']);

    // Feature config (nested under customer/group)
    Route::put('/customers/{customer}/groups/{group}/feature-config', [FeatureConfigController::class, 'update']);
});
```

### Controller Example

```php
<?php

namespace App\Http\Controllers\E2E;

use App\Data\Controllers\E2E\UserController\CreateUserRequestData;
use App\Data\Controllers\E2E\UserController\CreateUserResponseData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function store(Request $request): Response
    {
        $data = CreateUserRequestData::validateAndCreate($request->all());

        $user = User::factory()->create([
            'name' => $data->name ?? 'E2E Test User',
            'email' => $data->email ?? 'e2e-' . Str::random(12) . '@test.example.com',
            'password' => bcrypt('password'),
        ]);

        return CreateUserResponseData::from([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',  // Plain text for tests
        ])->toResponse($request)->setStatusCode(201);
    }

    public function destroy(User $user): Response
    {
        $user->forceDelete();

        return response()->noContent();
    }
}
```

### Data Classes

Use Laravel Data classes with `#[TypeScript]` for auto-generated frontend types:

```php
<?php

namespace App\Data\Controllers\E2E\UserController;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CreateUserResponseData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
```

Regenerate types after changes:

```bash
php artisan typescript:transform
```

## Key Principles

1. **Environment Guard** - E2E routes must NOT exist in production
2. **One Resource Per Controller** - RESTful, independent resources
3. **Use Factories** - Leverage Laravel factories for realistic data
4. **Generated Types** - Use auto-generated TypeScript types, never duplicate
5. **Automatic Cleanup** - Fixtures clean up after each test
6. **Plain Text Password** - Return password in response for login tests
7. **Extend BaseE2EApi** - All API clients inherit shared HTTP patterns

## Adding a New Resource

See `fixture-organization.md` for detailed steps:

1. Create backend controller at `app/Http/Controllers/E2E/{Resource}Controller.php`
2. Create Data classes at `app/Data/Controllers/E2E/{Resource}Controller/`
3. Add routes to `routes/e2e.php`
4. Run `php artisan typescript:transform`
5. Create frontend API client at `e2e/fixtures/api/{Resource}E2EApi.ts`
6. Export from `e2e/fixtures/api/index.ts`
7. Optionally add to `TestDataOrchestrator`

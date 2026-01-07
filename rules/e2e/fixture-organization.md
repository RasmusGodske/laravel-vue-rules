---
paths: e2e/fixtures/**/*.ts
---

# E2E API Client Organization

E2E test fixtures use **API clients** (`*E2EApi` classes) to communicate with backend E2E controllers for test data creation and cleanup.

## Directory Structure

```
e2e/fixtures/
├── api/                        # E2E API clients (flat structure)
│   ├── BaseE2EApi.ts           # Shared HTTP patterns - all clients extend this
│   ├── CustomerE2EApi.ts       # Customer resource API
│   ├── UserE2EApi.ts           # User resource API
│   ├── CustomerUserE2EApi.ts   # CustomerUser pivot API
│   ├── FeatureConfigE2EApi.ts  # FeatureConfig resource API
│   └── index.ts                # Barrel export
├── core/
│   └── TestDataOrchestrator.ts # High-level test setup convenience
├── test-data.ts                # Public API exports
└── base.fixture.ts             # Playwright fixture definitions
```

**Pattern:** `e2e/fixtures/api/[Resource]E2EApi.ts`

## Naming Conventions

### Class Names: `[Resource]E2EApi`

| Resource | Class Name | Why "E2EApi"? |
|----------|------------|---------------|
| Customer | `CustomerE2EApi` | Prevents conflict with future `CustomerApi` for real API testing |
| User | `UserE2EApi` | Clear purpose: E2E test data operations |
| CustomerUser | `CustomerUserE2EApi` | Pivot resource has its own API |
| FeatureConfig | `FeatureConfigE2EApi` | Consistent naming |

### File Names: Match Class Names

```
CustomerE2EApi.ts     → exports class CustomerE2EApi
UserE2EApi.ts         → exports class UserE2EApi
CustomerUserE2EApi.ts → exports class CustomerUserE2EApi
```

### Why NOT "Helper"?

"Helper" is vague. These classes are **API clients** that:
- Make HTTP requests to E2E endpoints
- Handle responses and errors
- Provide type-safe methods for test data operations

"E2EApi" clearly communicates: "This is an API client for E2E test data."

## Rules

### 1. One API Client Per Resource (RESTful)

Each backend E2E controller MUST have exactly ONE corresponding TypeScript API client.

```
Backend: app/Http/Controllers/E2E/CustomerController.php
         ↓ mirrors ↓
Frontend: e2e/fixtures/api/CustomerE2EApi.ts
```

**CRITICAL:** Do NOT combine resources. Each resource is independent:

```typescript
// ✅ CORRECT - Separate resources
CustomerE2EApi     → POST /e2e/customers
UserE2EApi         → POST /e2e/users
CustomerUserE2EApi → POST /e2e/customer-users

// ❌ WRONG - Combined resources
UserE2EApi → POST /e2e/customers/{id}/users  // Creates User AND CustomerUser
```

### 2. Extend BaseE2EApi

All API clients MUST extend `BaseE2EApi` for shared HTTP patterns.

```typescript
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

**Benefits:**
- Consistent error handling across all API clients
- Shared HTTP request patterns (post, put, delete)
- Less boilerplate code

### 3. Use Generated TypeScript Types

API clients MUST use auto-generated types from Laravel Data classes.

```typescript
// ✅ CORRECT - Using generated types
import type { App } from '@/types/generated'

type CreateUserResponse = App.Data.Controllers.E2E.UserController.CreateUserResponseData
```

```typescript
// ❌ WRONG - Custom duplicate types
interface CreateUserResponse {
  id: number
  email: string
  name: string
  password: string
}
```

**Why:** Single source of truth - if backend Data class changes, TypeScript types update automatically.

### 4. Method Naming Convention

API client methods should use clear, RESTful names:

| Backend Action | Frontend Method | Example |
|----------------|-----------------|---------|
| `store()` | `create()` | `customerApi.create({ name: 'Test' })` |
| `update()` | `update()` | `userApi.update(id, { name: 'New' })` |
| `destroy()` | `delete()` | `customerApi.delete(customerId)` |
| `show()` | `get()` | `userApi.get(userId)` |
| `index()` | `list()` | `customerApi.list()` |

### 5. Flat File Structure

API clients live in a FLAT directory structure - no nesting by controller:

```
✅ CORRECT - Flat structure
e2e/fixtures/api/
├── BaseE2EApi.ts
├── CustomerE2EApi.ts
├── UserE2EApi.ts
├── CustomerUserE2EApi.ts
└── index.ts

❌ WRONG - Nested structure
e2e/fixtures/api/
├── CustomerController/
│   ├── CustomerE2EApi.ts
│   └── index.ts          ← Unnecessary nesting
└── UserController/
    ├── UserE2EApi.ts
    └── index.ts
```

**Why flat:**
- One class per file = easy to find
- No need for per-resource barrel files
- Scales to 200+ resources without deep nesting

### 6. Export from api/index.ts

All API clients are exported from the barrel file:

```typescript
// e2e/fixtures/api/index.ts
export { BaseE2EApi } from './BaseE2EApi'
export { CustomerE2EApi } from './CustomerE2EApi'
export { UserE2EApi } from './UserE2EApi'
export { CustomerUserE2EApi } from './CustomerUserE2EApi'
export { FeatureConfigE2EApi } from './FeatureConfigE2EApi'
```

**Usage:**
```typescript
import { CustomerE2EApi, UserE2EApi } from '@/fixtures/api'
```

## Adding a New E2E API Client

When creating a new E2E resource (e.g., `Order`):

### Step 1: Create Backend Controller

```php
// app/Http/Controllers/E2E/OrderController.php
class OrderController extends Controller
{
    public function store(Request $request, Customer $customer): Response
    {
        $data = CreateOrderRequestData::validateAndCreate($request->all());
        // ... create order
        return CreateOrderResponseData::from([...])->toResponse(request())->setStatusCode(201);
    }
}
```

### Step 2: Create Backend Data Classes

```
app/Data/Controllers/E2E/OrderController/
├── CreateOrderRequestData.php
└── CreateOrderResponseData.php
```

### Step 3: Add Routes

```php
// routes/e2e.php
Route::post('/e2e/customers/{customer}/orders', [OrderController::class, 'store']);
Route::delete('/e2e/orders/{order}', [OrderController::class, 'destroy']);
```

### Step 4: Regenerate TypeScript Types

```bash
php artisan typescript:transform
```

### Step 5: Create Frontend API Client

```typescript
// e2e/fixtures/api/OrderE2EApi.ts
import { BaseE2EApi } from './BaseE2EApi'
import type { App } from '@/types/generated'

type CreateOrderResponse = App.Data.Controllers.E2E.OrderController.CreateOrderResponseData

export class OrderE2EApi extends BaseE2EApi {
  async create(
    customerId: number,
    options?: { total?: number }
  ): Promise<CreateOrderResponse> {
    return this.post<CreateOrderResponse>(`/e2e/customers/${customerId}/orders`, {
      total: options?.total,
    })
  }

  async delete(orderId: number): Promise<void> {
    return super.delete(`/e2e/orders/${orderId}`)
  }
}
```

### Step 6: Export from Barrel

```typescript
// e2e/fixtures/api/index.ts
export { OrderE2EApi } from './OrderE2EApi'
```

### Step 7: Add to TestDataOrchestrator (Optional)

If the resource is commonly used, add it to the orchestrator:

```typescript
// e2e/fixtures/core/TestDataOrchestrator.ts
public readonly orders: OrderE2EApi

constructor(request: APIRequestContext, baseURL: string) {
  // ...
  this.orders = new OrderE2EApi(request, baseURL)
}
```

## Summary

### DO:
- Name classes `[Resource]E2EApi`
- Use flat file structure in `api/` directory
- Extend `BaseE2EApi` for all API clients
- Use generated TypeScript types
- One resource per API client (RESTful)
- Export from `api/index.ts`

### DON'T:
- Use "Helper" in names
- Create nested directory structures
- Duplicate TypeScript types
- Combine multiple resources in one API client
- Skip the barrel export

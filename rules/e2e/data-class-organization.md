---
paths: app/Data/Controllers/E2E/**/*.php
---

# E2E Data Class Organization

E2E Data classes must follow the nested controller structure to maintain consistency with the rest of the codebase.

## Directory Structure

```
app/Data/Controllers/E2E/
├── CustomerController/
│   └── CreateCustomerResponseData.php
├── UserController/
│   ├── CreateUserRequestData.php
│   ├── CreateUserResponseData.php
│   └── UserData.php
├── CustomerUserController/          # Pivot controller for multi-tenant apps
│   ├── CreateCustomerUserRequestData.php
│   ├── CreateCustomerUserResponseData.php
│   └── CustomerUserData.php
└── FeatureConfigController/
    └── UpdateFeatureConfigRequestData.php
```

**Pattern:** `app/Data/Controllers/E2E/[ControllerName]/[DataClass].php`

## Rules

### 1. Mirror Backend Controller Structure

Data classes MUST be organized under `app/Data/Controllers/E2E/` with one subdirectory per controller.

```php
// ✅ CORRECT - Nested by controller
namespace App\Data\Controllers\E2E\UserController;

class CreateUserResponseData extends Data { ... }
```

```php
// ❌ WRONG - Flat structure
namespace App\Data\E2E;

class CreateUserResponseData extends Data { ... }
```

**Why:** Matches the pattern used by App/Adm controllers, making the codebase consistent and easier to navigate.

### 2. Use TypeScript Annotation

All E2E Data classes MUST include `#[TypeScript]` annotation for auto-generation of TypeScript types.

```php
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CreateUserResponseData extends Data { ... }
```

**Why:** E2E tests are written in TypeScript and need access to these types.

### 3. Add Static Factory Methods

Response Data classes SHOULD include static factory methods for convenient creation from models.

```php
/**
 * Create from CustomerUser model
 */
public static function fromCustomerUser(CustomerUser $customerUser): self
{
    return new self(
        user: UserData::fromUser($customerUser->user),
        customer_user: CustomerUserData::fromCustomerUser($customerUser),
    );
}
```

**Benefits:**
- Controller code becomes cleaner (no manual array construction)
- Data class encapsulates its own creation logic
- Type-safe transformation from models to DTOs

**Usage in controller:**
```php
// ✅ GOOD - Using static factory method
return CreateUserResponseData::fromCustomerUser($customerUser)
    ->toResponse(request())
    ->setStatusCode(201);

// ❌ BAD - Manual array construction
return CreateUserResponseData::from([
    'user' => UserData::from([...]),
    'customer_user' => CustomerUserData::from([...]),
])->toResponse(request())->setStatusCode(201);
```

### 4. Name Factory Methods Clearly

Factory method names should indicate the source model:
- `fromCustomerUser(CustomerUser $customerUser)`
- `fromUser(User $user)`
- `fromCustomer(Customer $customer)`

NOT generic names like:
- ❌ `create()` - Too generic
- ❌ `make()` - Doesn't indicate source
- ❌ `new()` - Conflicts with constructor

## Generated TypeScript Types

TypeScript types are auto-generated to:
```
resources/js/types/generated.d.ts
```

**Access in TypeScript:**
```typescript
import type { App } from '@/types/generated'

type CreateUserResponse = App.Data.Controllers.E2E.UserController.CreateUserResponseData
```

## Summary

✅ **DO:**
- Nest Data classes under `app/Data/Controllers/E2E/[ControllerName]/`
- Use `#[TypeScript]` annotation on all classes
- Add static factory methods (e.g., `fromCustomerUser()`)
- Follow the same pattern as App/Adm controllers

❌ **DON'T:**
- Use flat directory structure (`app/Data/E2E/`)
- Manually construct Data objects in controllers
- Skip TypeScript annotation

---
paths:
  - app/**/*.php
  - resources/js/**/*.{vue,ts,tsx}
  - e2e/**/*.ts
---

# No Hardcoded Database Entity IDs or Names

**Core Principle:** NEVER hardcode database entity IDs, names, or other identifiers. Always use enums, constants, or generated types.

## The Problem

Database entities (user groups, roles, statuses, etc.) are defined with specific IDs or names that can differ between environments and change over time. Hardcoding them creates fragile dependencies.

**Examples of violations:**
```typescript
// ❌ BAD - Hardcoded IDs
const groupId = userType === 'admin' ? 2 : 3

// ❌ BAD - Hardcoded string literals
if (user.role === 'admin') { ... }
if (status === 'active') { ... }

// ❌ BAD - Magic numbers
$user->group_id = 2;  // What is 2?
```

**Why this is wrong:**
- IDs differ between environments (local/staging/production)
- String literals allow typos (`'payed'` vs `'paid'`)
- Changes require finding all hardcoded values
- No type safety or refactoring support

## The Solution: LaravelData with TypeScript Generation

Use PHP enums with `#[Typescript]` annotation to create a single source of truth that generates TypeScript types automatically.

### Architecture Flow

```
PHP Enum (source of truth)
    ↓ (php artisan typescript:generate)
TypeScript Types (auto-generated)
    ↓ (used in)
Backend, Frontend, E2E Tests
```

### 1. Define PHP Enum

```php
<?php
namespace App\Enums;

enum UserGroupEnum: int
{
    case STAFF = 1;
    case ADMIN = 2;
    case SALES = 3;
}
```

### 2. Use in Backend

```php
use App\Enums\UserGroupEnum;

// ✅ GOOD
$groupId = UserGroupEnum::ADMIN->value;
if ($user->group_id === UserGroupEnum::ADMIN->value) { ... }
```

### 3. Generate TypeScript Types

```bash
php artisan typescript:generate
```

### 4. Use in Frontend/E2E

```typescript
// ✅ GOOD - Runtime values
import { App } from '@/types/enums'
const adminId = App.Enums.UserGroupEnum.ADMIN

// ✅ GOOD - Type annotation (no import needed)
interface User {
  groupId: App.Enums.UserGroupEnum
}
```

## When to Create an Enum

Create a backed enum whenever you have:
- Fixed database entities (user groups, roles, permissions)
- Status values (order status, payment status)
- Configuration options (currency codes, feature flags)
- Type discriminators (calculation methods, entity types)

## Common Violations

### E2E Test APIs

```typescript
// ❌ BAD - String literal parameters
async createUser(userGroup: 'Admin' | 'Staff') {
  const groupId = userGroup === 'Admin' ? 2 : 3  // Hardcoded!
}

// ✅ GOOD - Enum parameters
import { App } from '@/types/enums'

async createUser(groupId: App.Enums.UserGroupEnum) {
  // Use groupId directly - no mapping needed
}

// Usage
createUser(App.Enums.UserGroupEnum.ADMIN)  // Type-safe!
```

### Test Files

```php
// ❌ BAD - String literal in tests
$group = Group::where('name', 'Administrator')->first();

// ✅ GOOD - Use enum
use App\Enums\UserGroupEnum;
$group = Group::find(UserGroupEnum::ADMINISTRATOR->value);
```

## Migration Steps

1. **Check if enum exists:** Search `app/Enums/` for existing enums
2. **Create enum if needed:** Define backed enum with values
3. **Regenerate types:** Run `php artisan typescript:generate`
4. **Replace hardcoded values:**
   - Backend: Use `EnumName::CASE->value`
   - Frontend: Use `App.Enums.EnumName.CASE`
   - E2E: Same as frontend

## Benefits

✅ **Single source of truth** - Define once in PHP
✅ **Type safety** - Compile-time checking in TypeScript
✅ **Refactor-safe** - Rename propagates everywhere
✅ **Environment-safe** - No ID mismatches
✅ **IntelliSense** - Autocomplete for all values

## Summary

**Don't hardcode. Use enums.**

1. ❌ Never hardcode database IDs or names
2. ✅ Always use PHP enums with `#[Typescript]` annotation
3. ✅ Always use generated types in frontend/E2E
4. ✅ Always regenerate after enum changes

Every hardcoded value is a future bug.

---
paths: app/**/*.php
---

# PHP/Laravel Backend Development Conventions

This document provides comprehensive PHP/Laravel development conventions. Follow these patterns to ensure code consistency, maintainability, and prevent common mistakes that lead to technical debt.

## Critical Backend Conventions

### 1. Always Import Classes at Top

**NEVER use fully qualified class names inline.** Always import at the top of the file.

```php
// ✅ CORRECT
use App\Data\User\ProfileData;
use App\Data\Settings\PreferencesData;

class User extends Model
{
    protected $casts = [
        'preferences' => PreferencesData::class,
        'profile' => ProfileData::class,
    ];
}

// ❌ WRONG - Inline fully qualified names
class User extends Model
{
    protected $casts = [
        'profile' => \App\Data\User\ProfileData::class,
    ];
}
```

### 2. Data Classes for JSON Columns (Critical)

**NEVER cast JSON columns to `array`.** Always create Spatie Laravel Data classes.

#### Why This Matters

Casting to `array` creates technical debt:
- Future developers don't know the JSON structure
- No IDE autocomplete or type safety
- No validation or type checking
- Difficult to refactor or trace usage

#### The Pattern

**Step 1:** Create a Data class in `App\Data\` with nested structure:

```php
<?php

namespace App\Data\User;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * User profile data including preferences and settings
 */
#[TypeScript()]
class ProfileData extends Data
{
    /**
     * @param Collection<int, SocialLinkData> $socialLinks User's social media links
     */
    public function __construct(
        public string $bio,
        public ?string $avatar,
        #[DataCollectionOf(SocialLinkData::class)]
        public Collection $socialLinks,
    ) {}
}
```

**Step 2:** Import and cast in the Eloquent model:

```php
use App\Data\User\ProfileData;

class User extends Model
{
    protected $casts = [
        'profile' => ProfileData::class,
    ];
}
```

**Directory Structure:** Use nested directories, not flat:

```
app/Data/
├── User/
│   ├── ProfileData.php
│   └── SocialLinkData.php
├── Order/
│   ├── ShippingData.php
│   └── PaymentData.php
└── Settings/
    └── PreferencesData.php
```

### 3. PHPDoc Type Annotations

**Always specify types** for Collections and Arrays in PHPDoc:

```php
// ✅ CORRECT - Item types specified
/**
 * @param Collection<int, User> $users
 * @return array<int, string>
 */
public function getUserNames(Collection $users): array
{
    return $users->pluck('name')->toArray();
}

// ❌ WRONG - Missing item types
/**
 * @param Collection $users
 * @return array
 */
public function getUserNames(Collection $users): array
```

**Common patterns:**
- Collections: `Collection<int, User>`
- Arrays: `array<int, string>` or `string[]`
- Array shapes: `array{id: int, name: string, active: bool}`

### 4. Named Arguments for Clarity

Use named arguments when parameter purpose isn't immediately obvious:

```php
// ✅ CORRECT - Clear intent
OrderService::create(
    customer: $customer,
    items: $items,
    shippingAddress: $address,
    paymentMethod: $paymentMethod
);

// ❌ WRONG - Unclear parameters
OrderService::create($customer, $items, $address, $paymentMethod);
```

### 5. Type Safety

Always use type hints and return types. Avoid `mixed` when possible:

```php
// ✅ CORRECT
public function getUser(int $id): ?User
{
    return User::find($id);
}

// ❌ WRONG - No types
public function getUser($id)
{
    return User::find($id);
}
```

### 6. Loops vs Collection Methods

**Prefer traditional foreach loops** when:
- Logic is complex with multiple steps
- Need `break`, `continue`, or early returns
- Debugging is important (loops are easier to debug)

```php
// ✅ CORRECT - Complex logic in loop
foreach ($users as $user) {
    if (!$user->isActive()) {
        continue;
    }

    if ($user->needsVerification()) {
        $this->sendVerificationEmail($user);
        break;
    }

    $this->processUser($user);
}
```

### 7. Service Container

Prefer `app()->make()` for better type handling:

```php
// ✅ CORRECT
$service = app()->make(DataObjectService::class);

// ❌ AVOID
$service = app(DataObjectService::class);
```

## Additional Conventions

### Early Returns

Check invalid scenarios first to reduce nesting:

```php
// ✅ CORRECT
public function process(User $user): void
{
    if (!$user->isActive()) {
        throw new UserInactiveException();
    }

    // Main logic with minimal nesting
    $this->performProcessing($user);
}
```

### Validation

Always use Form Request classes:

```php
// ✅ CORRECT
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
```

### Enums Over Strings

Use enums for new code:

```php
// ✅ CORRECT
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
}

public function setStatus(OrderStatus $status): void
```

## Quick Checklist

Before committing PHP code, verify:

- [ ] All classes imported at top (no `\Fully\Qualified\Names`)
- [ ] JSON columns use Data classes (no `'column' => 'array'` casts)
- [ ] PHPDoc includes Collection/Array item types (`Collection<int, User>`)
- [ ] Named arguments used where parameters aren't obvious
- [ ] Type hints and return types on all methods
- [ ] Proper early returns to reduce nesting

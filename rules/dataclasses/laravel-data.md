---
paths: app/Data/**/*.php
---

# Spatie Laravel Data Classes

Guide for creating and working with Spatie Laravel Data classes. These classes provide structured, validated DTOs (Data Transfer Objects) for your Laravel application.

## 🚨 CRITICAL: Constructor Property Promotion

**This is the #1 mistake to avoid!** All properties MUST be defined in the constructor using property promotion.

### ❌ WRONG - Properties Outside Constructor

```php
class UserData extends Data
{
    // BAD - Don't define properties here!
    public string $name;
    public string $email;
    public int $age;

    public function __construct()
    {
        // Properties should be here instead
    }
}
```

### ✅ CORRECT - Properties in Constructor

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {}
}
```

**Why constructor property promotion:**
- Required by Spatie Laravel Data
- Automatic property initialization
- Works with validation annotations
- Type-safe and clean

## When to Create Data Classes

**IMPORTANT:** Don't create Data classes by default. Only create them when they provide real value. Overuse of Data classes leads to duplication and unnecessary maintenance burden.

### ✅ GOOD Use Cases - Create Data Classes For:

#### 1. Inertia Props (Controller Responses)

**Problem:** Returning raw arrays to Inertia loses type safety in the frontend.

**❌ BAD - Raw Array:**
```php
// App/Http/Controllers/DogController.php
return Inertia::render('Dogs/Show', [
    'dog' => [
        'id' => $dog->id,
        'name' => $dog->name,
        'breed' => $dog->breed,
        // ... 15 more properties
    ],
    'stats' => [
        'totalActivities' => 42,
        'averageWeight' => 25.5,
        'lastActivity' => '2025-01-15',
    ],
]);
```

**✅ GOOD - Dedicated Data Class:**
```php
// App/Data/Http/Controllers/DogController/DogShowPropsData.php
#[TypeScript()]
class DogShowPropsData extends Data
{
    public function __construct(
        public Dog $dog,

        #[DataCollectionOf(Dog::class)]
        public Collection $allDogs,

        public DogStatsData $stats,
        public ?MoodData $lastMood,
        public DailyStatsData $dailyFoodStats,
        public DailyStatsData $dailyDrinkStats,
        public DailyStatsData $dailyPeeStats,
        public DailyStatsData $dailyPoopStats,
    ) {}
}

// Controller becomes clean:
return Inertia::render('Dogs/Show', DogShowPropsData::from([
    'dog' => $dog,
    'allDogs' => $allDogs,
    'stats' => $stats,
    // ...
]));
```

**Benefits:**
- Frontend gets TypeScript types automatically
- Adding/removing props doesn't require manual frontend type updates
- Type-safe props in Vue components
- Centralized structure for the page's data contract

**Naming Convention:** `App/Data/Http/Controllers/{ControllerName}/{MethodName}PropsData.php`

#### 2. Service Methods Returning Aggregated Data

**Problem:** Returning arrays or objects from service methods loses type safety.

**❌ BAD - Returning Array:**
```php
class OrderStatisticsService
{
    public function calculateMonthlyStats(int $month): array
    {
        return [
            'total_orders' => 150,
            'total_revenue' => 15000.50,
            'average_order_value' => 100.00,
            'top_products' => [...],
        ];
    }
}
```

**✅ GOOD - Dedicated Data Class:**
```php
// App/Data/Services/OrderStatistics/MonthlyStatsData.php
class MonthlyStatsData extends Data
{
    public function __construct(
        public int $totalOrders,
        public float $totalRevenue,
        public float $averageOrderValue,

        #[DataCollectionOf(ProductStatsData::class)]
        public Collection $topProducts,
    ) {}
}

class OrderStatisticsService
{
    public function calculateMonthlyStats(int $month): MonthlyStatsData
    {
        // ...
        return MonthlyStatsData::from([...]);
    }
}
```

**Naming Convention:** `App/Data/Services/{ServiceName}/{MethodResult}Data.php`

#### 3. Complex JSON Column Data

**Problem:** Casting JSON columns to `array` loses type safety.

**✅ GOOD - Data Class for JSON:**
```php
// App/Data/Models/Product/SpecificationsData.php
class SpecificationsData extends Data
{
    public function __construct(
        public string $color,
        public string $size,
        public float $weight,
        public DimensionsData $dimensions,
    ) {}
}

// Model:
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'specifications' => SpecificationsData::class, // ✅ Type-safe
            // NOT 'array' ❌
        ];
    }
}
```

#### 4. API Response/Request Bodies

**✅ GOOD - When building APIs:**
```php
// App/Data/Api/V1/CreateOrderRequestData.php
class CreateOrderRequestData extends Data
{
    public function __construct(
        #[Required]
        public int $customerId,

        #[Required]
        #[DataCollectionOf(OrderItemData::class)]
        public Collection $items,
    ) {}
}

// App/Data/Controllers/App/OrderController/GetOrdersResponseData.php
#[TypeScript()]
class GetOrdersResponseData extends Data
{
    public function __construct(
        #[DataCollectionOf(OrderData::class)]
        public Collection $orders,
        public int $total,
    ) {}
}

// Controller
public function index(): JsonResponse
{
    $orders = Order::all();

    return response()->json(
        GetOrdersResponseData::from([
            'orders' => $orders,
            'total' => $orders->count(),
        ])
    );
}
```

**Why?**
- Frontend gets TypeScript types automatically
- Type-safe access to all properties
- Self-documenting API contract
- No need to manually maintain frontend types

**❌ NEVER return raw arrays from API endpoints:**
```php
// BAD - No type safety, no IntelliSense
public function index(): JsonResponse
{
    return response()->json([
        'orders' => Order::all(),
        'total' => Order::count(),
    ]);
}
```

### ❌ BAD Use Cases - DON'T Create Data Classes For:

#### 1. Wrapping Eloquent Models

**❌ NEVER DO THIS:**
```php
// BAD - Unnecessary wrapper with same properties
class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public Carbon $createdAt,
    ) {}
}

// Then using it like:
return Inertia::render('Users/Show', [
    'user' => UserData::from($user), // ❌ Pointless wrapper
]);
```

**✅ DO THIS INSTEAD:**
```php
// Just pass the model directly
return Inertia::render('Users/Show', [
    'user' => $user, // ✅ IDE helpers generate TypeScript for models
]);
```

**Why?**
- We have TypeScript generation for Eloquent models (`ide-helper:models`)
- Creates unnecessary duplication (two places to update when adding fields)
- No added value - just extra maintenance burden
- Models already have type hints and casts

**Exception:** Only wrap models if you need to:
- Add computed properties not in the database
- Transform/hide certain fields for security
- Combine data from multiple models

#### 2. Single Primitive Values

**❌ DON'T:**
```php
class UserCountData extends Data
{
    public function __construct(public int $count) {}
}
```

**✅ DO:**
```php
// Just return the value
return $userRepository->count(); // Returns int
```
### Decision Tree: Should I Create a Data Class?

Ask yourself:

1. **Is this an Eloquent model?** → ❌ NO, just use the model
2. **Am I returning an array/object from a method?** → ✅ YES, create Data class
3. **Am I building Inertia props?** → ✅ YES, create Data class for the entire props object
4. **Am I returning JSON from an API endpoint?** → ✅ **YES, ALWAYS create Data class**
5. **Is this a JSON column in the database?** → ✅ YES, create Data class
6. **Is this a single primitive value?** → ❌ NO, return the value directly
7. **Do I need validation?** → ✅ YES, create Data class with validation annotations

### Rule of Thumb

**Create a Data class when:**
- You would otherwise return an associative array (`['key' => 'value']`)
- **You're returning JSON from ANY endpoint (Inertia or API)**
- You need type safety for complex structures
- You're building a data contract between layers (service → controller → view)

**Don't create a Data class when:**
- You're just wrapping an existing typed object (like a Model)
- You're returning a single primitive value
- You're working with a simple indexed array of items

## Class Structure

### Basic Structure

```php
<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Email]
        public string $email,
    ) {}
}
```

### Key Requirements:

- ✅ Extend `Spatie\LaravelData\Data`
- ✅ All properties in constructor with `public` visibility
- ✅ Use validation annotations (attributes) above each property
- ✅ Each annotation on its own line
- ✅ Optional: Add `messages()` method for custom error messages
- ✅ Optional: Add static factory methods for convenience
- ✅ Optional: Add PHPDoc block with property descriptions

## 🚨 CRITICAL: Never Use request() in Data Classes

**This is the most common mistake that breaks tests!** Data classes may be instantiated outside HTTP context.

### ❌ WRONG - Using request() in Data Class

```php
// BLOCKED - This returns NULL in tests, queues, CLI!
class UpdateServerData extends Data
{
    public static function rules(ValidationContext $context): array
    {
        // ❌ NEVER DO THIS - request() returns null outside HTTP!
        $serverId = request()->route('server')?->id;

        return [
            'discord_channel_id' => [
                Rule::unique('servers')->ignore($serverId),
            ],
        ];
    }
}
```

**Why it fails:** `Data::validateAndCreate()` can be called from tests, queue jobs, or CLI commands where `request()` returns `null`.

### ✅ CORRECT - Pass Context Explicitly

```php
class UpdateServerData extends Data
{
    public function __construct(
        #[Max(255)]
        public string $name,

        public string $discord_channel_id,
    ) {}

    /**
     * Factory method with explicit server ID for unique validation
     */
    public static function validateAndCreateForServer(array $data, int $ignoreServerId): self
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'discord_channel_id' => [
                'required',
                'string',
                Rule::unique('servers', 'discord_channel_id')->ignore($ignoreServerId),
            ],
        ]);

        $validator->validate();
        return self::from($data);
    }
}

// Controller passes the ID explicitly
$data = UpdateServerData::validateAndCreateForServer(
    data: $request->all(),
    ignoreServerId: $server->id,
);
```

### Quick Test

**Ask yourself:** "Would this Data class work in `php artisan tinker`?"

If the answer is "no" because of `request()`, `auth()`, or `session()`:
1. Create a custom factory method that accepts the value explicitly
2. Have the controller pass the value when calling the factory method

---

## Validation with Annotations

**📚 For custom validation rules, see [custom-validation-rules.md](./custom-validation-rules.md) - Complete guide with working examples.**

**Use annotations, NOT manual rules!**

### ❌ WRONG - Manual Rules

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    // BAD - Don't manually define rules!
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];
    }
}
```

### ✅ CORRECT - Use Annotations

```php
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Email]
        public string $email,
    ) {}
}
```

### Common Validation Annotations

```php
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Url;

public function __construct(
    #[Required]
    #[StringType]
    public string $name,

    #[Nullable]
    #[StringType]
    public ?string $description,

    #[Required]
    #[IntegerType]
    #[Min(0)]
    #[Max(100)]
    public int $age,

    #[BooleanType]
    public bool $isActive = false,

    #[Required]
    #[Email]
    public string $email,

    #[Required]
    #[Url]
    public string $website,
) {}
```

**Important:** Each annotation should be on its own line for better readability.

## Collections: Use Collection, NOT Array

**Always use `Illuminate\Support\Collection` with `#[DataCollectionOf]` annotation!**

### ❌ WRONG - Using Array

```php
class TeamData extends Data
{
    public function __construct(
        public string $name,
        // BAD - Don't use array!
        public array $members,
    ) {}
}
```

### ✅ CORRECT - Using Collection

```php
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class TeamData extends Data
{
    public function __construct(
        public string $name,

        #[DataCollectionOf(MemberData::class)]
        public Collection $members,
    ) {}
}
```

**Why Collection over array:**
- Better type safety
- Proper transformation of nested Data objects
- Refactoring-friendly (IDE support)
- Collection helper methods available
- Recommended by Spatie

## Working with Enums

**Always use `#[WithCast(EnumCast::class)]` for enum properties:**

```php
use App\Enums\UserStatus;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Attributes\Validation\Required;

class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[WithCast(EnumCast::class)]
        public UserStatus $status,
    ) {}
}
```

## Optional Fields

**Use `Optional|null|Type $prop = new Optional` pattern:**

```php
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    public function __construct(
        // Required field
        #[Required]
        public string $name,

        // Optional field - can be omitted, null, or string
        public Optional|null|string $nickname = new Optional,

        // Optional integer
        public Optional|null|int $age = new Optional,
    ) {}
}
```

**Why this pattern:**
- Allows field to be omitted entirely
- Allows `null` value
- Works correctly with validation
- No IDE complaints

## Custom Validation Messages

**Use static `messages()` method:**

```php
class RegisterUserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[Min(8)]
        public string $password,
    ) {}

    public static function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
```

## TypeScript Export

**Add `#[TypeScript()]` attribute to export to frontend:**

```php
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
```

This generates TypeScript types that can be used in your frontend code.

## PHPDoc Documentation

**Add PHPDoc blocks for better IDE support:**

```php
/**
 * User registration data
 *
 * @property string $name User's full name
 * @property string $email User's email address
 * @property Collection<int, AddressData> $addresses User's addresses
 */
class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        #[DataCollectionOf(AddressData::class)]
        public Collection $addresses,
    ) {}
}
```

**Important:** For Collection properties, always specify: `Collection<int, ValueType>`

## Static Factory Methods

**Add convenience factory methods:**

```php
class AddressData extends Data
{
    public function __construct(
        #[Required]
        public string $street,

        #[Required]
        public string $city,

        #[Required]
        public string $country,

        #[Required]
        public string $postalCode,

        public bool $isPrimary = false,
    ) {}

    /**
     * Create a primary address
     */
    public static function primary(
        string $street,
        string $city,
        string $country,
        string $postalCode
    ): self {
        return self::from([
            'street' => $street,
            'city' => $city,
            'country' => $country,
            'postal_code' => $postalCode,
            'is_primary' => true,
        ]);
    }
}
```

## Complete Example

```php
<?php

namespace App\Data;

use App\Enums\UserStatus;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * User profile data
 *
 * @property string $name User's full name
 * @property string $email User's email address
 * @property Collection<int, AddressData> $addresses User's addresses
 */
#[TypeScript()]
class UserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[WithCast(EnumCast::class)]
        public UserStatus $status,

        #[DataCollectionOf(AddressData::class)]
        public Collection $addresses,

        #[Nullable]
        public ?string $bio = null,

        public Optional|null|int $age = new Optional,
    ) {}

    /**
     * Get the primary address
     */
    public function getPrimaryAddress(): ?AddressData
    {
        return $this->addresses->first(
            fn (AddressData $address) => $address->isPrimary
        );
    }

    /**
     * Custom validation error messages
     */
    public static function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
```

## Checklist for Data Classes

Before considering a Data class complete:

- ✅ Extends `Spatie\LaravelData\Data`
- ✅ All properties defined in constructor (NOT outside)
- ✅ Uses validation annotations (NOT manual rules())
- ✅ Each annotation on its own line
- ✅ Uses `Collection` for collections (NOT array)
- ✅ Uses `#[DataCollectionOf(Class::class)]` for collections
- ✅ Uses `Optional|null|Type $prop = new Optional` for optional fields
- ✅ Uses `#[WithCast(EnumCast::class)]` for enums
- ✅ Has `messages()` method for custom error messages (if needed)
- ✅ Has PHPDoc block documenting properties
- ✅ Collection PHPDoc uses `Collection<int, Type>` format
- ✅ Has `#[TypeScript()]` if used in frontend
- ✅ Has static factory methods for common use cases (if applicable)

## Common Imports

```php
// Base
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

// Collections
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;

// Validation
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Url;

// Casts
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;

// TypeScript
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
```

## Final Reminder

**Top 4 mistakes to avoid:**

1. ❌ **Using request() in Data classes** - Breaks tests, queues, CLI (see CRITICAL section above)
2. ❌ **Defining properties outside constructor** - Always use constructor property promotion
3. ❌ **Writing manual rules()** - Always use validation annotations
4. ❌ **Using array instead of Collection** - Always use `Collection` with `#[DataCollectionOf]`

### Detection Checklist

Before submitting Data class code, search for:

- [ ] `request()->` - NEVER use in Data classes
- [ ] `auth()->` - NEVER use in Data classes
- [ ] `session()->` - NEVER use in Data classes
- [ ] Properties defined outside constructor - Should be in constructor with promotion
- [ ] `public function rules()` - Should use validation annotations instead
- [ ] `public array $items` - Should be `Collection` with `#[DataCollectionOf]`

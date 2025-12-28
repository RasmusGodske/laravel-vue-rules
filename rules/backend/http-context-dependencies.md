---
paths: app/**/*.php
---

# HTTP Context Dependencies

## 🚨 CRITICAL: Most Common Mistake in This Codebase

**30% of blocked agent reviews violated this rule.** Read this carefully.

```php
// ❌ NEVER DO THIS - Returns NULL in tests, CLI, queues!
$serverId = request()->route('server')?->id;
$user = request()->user();
$value = request()->input('field');
```

**Simple test:** Would this code work in `php artisan tinker`? If no, you're using HTTP context.

---

**Rule: Never use HTTP context helpers (`request()`, `auth()`, `session()`) outside of controllers. Always pass dependencies explicitly.**

## The Problem

HTTP context helpers like `request()`, `auth()`, and `session()` only work during an HTTP request. They fail or return unexpected values in:

- **Unit tests** - No HTTP request exists
- **Feature tests** - May not have the expected request data
- **Artisan commands** - CLI context, no HTTP request
- **Queue jobs** - Async context, no HTTP request
- **Validation attributes** - May be instantiated outside HTTP context
- **Service classes** - Should be context-agnostic
- **Tinker** - REPL context, no HTTP request

## Helpers to Avoid

| Helper | Problem | Use Instead |
|--------|---------|-------------|
| `request()` | Returns null/empty outside HTTP | Pass `Request $request` as parameter |
| `request()->input()` | Returns null outside HTTP | Pass the specific value as parameter |
| `request()->user()` | Returns null outside HTTP | Pass `User $user` as parameter |
| `auth()` / `Auth::user()` | Returns null outside HTTP | Pass `User $user` as parameter |
| `session()` | Doesn't work outside HTTP | Pass values explicitly or use cache |

**Note:** `app()` is fine - it's a service locator that works in all contexts.

## Controllers: Always Use Injected Request

Even in controllers, prefer the injected `Request` over the `request()` helper:

```php
// ❌ WRONG - Using helper
class ProductController extends Controller
{
    public function store()
    {
        $name = request()->input('name');
        $user = request()->user();
        // ...
    }
}

// ✅ CORRECT - Using injected request
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $name = $request->input('name');
        $user = $request->user();
        // ...
    }
}
```

**Why?** Consistency. If you use `request()` in controllers, you'll accidentally use it elsewhere too.

## Services: Pass Values Explicitly

Services should never access HTTP context. Pass what they need:

```php
// ❌ WRONG - Service accessing request
class OrderService
{
    public function createOrder(array $items)
    {
        $user = request()->user();  // Fails in tests, queues, CLI
        $currency = request()->input('currency');
        // ...
    }
}

// ✅ CORRECT - Dependencies passed explicitly
class OrderService
{
    public function createOrder(
        User $user,
        array $items,
        string $currency,
    ) {
        // All dependencies are explicit and testable
    }
}
```

## Validation Attributes: Never Use request()

Custom validation attributes are instantiated during rule collection, which may happen outside HTTP context:

```php
// ❌ WRONG - Will fail in tests and non-HTTP contexts
#[Attribute(Attribute::TARGET_PROPERTY)]
class MutuallyExclusiveWith extends CustomValidationAttribute
{
    public function __construct(private string $otherField) {}

    public function getRules(ValidationPath $path): array
    {
        // NEVER DO THIS - request() returns null in tests!
        $otherValue = request()->input($this->otherField);

        if ($otherValue !== null) {
            return ['declined'];
        }
        return [];
    }
}

// ✅ CORRECT - Use Laravel's built-in rules with FieldReference
#[Attribute(Attribute::TARGET_PROPERTY)]
class MutuallyExclusiveWith extends CustomValidationAttribute
{
    protected FieldReference $field;

    public function __construct(string|FieldReference $field)
    {
        $this->field = is_string($field) ? new FieldReference($field) : $field;
    }

    public function getRules(ValidationPath $path): array
    {
        $fieldName = $this->field->getValue($path);

        // Use Laravel's built-in rule - it handles context correctly
        return ["prohibited_if:{$fieldName},!=,"];
    }
}
```

**For cross-field validation in Data classes**, prefer:
1. `CustomValidationAttribute` with `FieldReference` (generates Laravel rule strings)
2. Laravel's built-in rules (`prohibited_if`, `required_without`, etc.)
3. The Data class's `rules()` method for complex cross-field logic

## Queue Jobs: Pass All Data Upfront

Queue jobs run asynchronously - there's no HTTP request:

```php
// ❌ WRONG - No request in queue context
class ProcessOrderJob implements ShouldQueue
{
    public function handle()
    {
        $user = auth()->user();  // Returns null!
        // ...
    }
}

// ✅ CORRECT - Pass data when dispatching
class ProcessOrderJob implements ShouldQueue
{
    public function __construct(
        private Order $order,
        private User $user,
    ) {}

    public function handle()
    {
        // Use $this->user - it was serialized with the job
    }
}

// Dispatch with all required data
ProcessOrderJob::dispatch($order, $request->user());
```

## Testing Benefits

When you avoid HTTP context helpers, testing becomes straightforward:

```php
// Service that doesn't use request() - easy to test
public function testOrderCreation()
{
    $user = User::factory()->create();
    $items = [/* ... */];

    $order = $this->orderService->createOrder(
        user: $user,
        items: $items,
        currency: 'USD',
    );

    $this->assertEquals($user->id, $order->user_id);
}
```

No need for:
- Mocking `request()`
- Setting up fake HTTP context
- Using `$this->actingAs()` for non-HTTP tests

## Quick Reference

**Ask yourself:** "Will this code work in a queue job or CLI command?"

If the answer is "no" because of `request()`, `auth()`, or `session()`:
1. Extract the HTTP-dependent values in the controller
2. Pass them as explicit parameters to services/jobs/etc.

```php
// Controller extracts HTTP context
public function store(Request $request)
{
    $this->orderService->create(
        user: $request->user(),           // Extracted here
        data: $request->validated(),       // Extracted here
        ipAddress: $request->ip(),         // Extracted here
    );
}

// Service is context-agnostic
class OrderService
{
    public function create(User $user, array $data, string $ipAddress)
    {
        // Works in HTTP, CLI, queues, tests - anywhere
    }
}
```

## Summary

| Context | `request()` works? | `auth()` works? | Best Practice |
|---------|-------------------|-----------------|---------------|
| Controller | ✅ Yes | ✅ Yes | Use injected `Request $request` |
| Service | ❌ No | ❌ No | Pass values as parameters |
| Validation Attribute | ❌ No | ❌ No | Use FieldReference + Laravel rules |
| Queue Job | ❌ No | ❌ No | Pass data in constructor |
| Artisan Command | ❌ No | ❌ No | Pass values as parameters |
| Unit Test | ❌ No | ❌ No | Pass values directly |

**The rule is simple:** Controllers are the boundary. Extract HTTP context there, pass it explicitly everywhere else.

---

## Common Mistakes (From Real Agent Reviews)

These mistakes were caught in code review and blocked from merging:

### ❌ Mistake 1: Using request() in Spatie Data Classes

```php
// BLOCKED - Agent tried this in UpdateServerData
class UpdateServerData extends Data
{
    public static function rules(ValidationContext $context): array
    {
        // ❌ WRONG - request() returns null in tests!
        $serverId = request()->route('server')?->id;

        return [
            'discord_channel_id' => [
                Rule::unique('servers')->ignore($serverId),
            ],
        ];
    }
}
```

**Why it fails:** Data class validation runs during `::validateAndCreate()`, which may be called from tests, queue jobs, or CLI commands where `request()` returns `null`.

**Correct approach:** Pass the server ID explicitly:

```php
// ✅ CORRECT - Factory method with explicit parameter
class UpdateServerData extends Data
{
    public static function validateAndCreateForServer(array $data, int $ignoreServerId): self
    {
        $validator = Validator::make($data, [
            'discord_channel_id' => [
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

### ❌ Mistake 2: request() in Custom Validation Attributes

```php
// BLOCKED - Agent tried accessing other field values via request()
public function validate(string $attribute, mixed $value, Closure $fail): void
{
    // ❌ WRONG - Silently fails in tests (returns null)
    $otherValue = request()->input('other_field');

    if ($value && $otherValue) {
        $fail('Both fields cannot be set.');
    }
}
```

**Correct approach:** Use `FieldReference` and Laravel's built-in rules (see Validation Attributes section above).

### Detection Checklist

Before submitting code, search for these patterns:

- [ ] `request()->` outside of controller methods
- [ ] `auth()->` outside of controller methods
- [ ] `session()->` outside of controller methods
- [ ] `request()` in any Data class
- [ ] `request()` in any Service class
- [ ] `request()` in any validation attribute

---
paths: app/Data/**/*.php, app/Rules/**/*.php
---

# Custom Validation Rules in Laravel Data

**Complete guide with working examples.**

Laravel Data's validation system is powerful but poorly documented. This guide demystifies custom validation, showing you exactly how to create your own rules with full control over validation logic.

---

## Table of Contents

1. [Three Approaches to Custom Validation](#three-approaches)
2. [Understanding How Validation Works](#how-validation-works)
3. [FieldReference: The Missing Manual](#fieldreference)
4. [Working Examples](#working-examples)
5. [Common Pitfalls](#common-pitfalls)
6. [Decision Tree](#decision-tree)

---

## Three Approaches to Custom Validation {#three-approaches}

Laravel Data provides three distinct ways to create custom validation rules. Each has different strengths:

| Approach | Best For | Complexity | Access to Other Fields |
|----------|----------|------------|------------------------|
| **1. CustomValidationAttribute** | Combining existing Laravel rules | ⭐ Simple | ✅ Via FieldReference |
| **2. ObjectValidationAttribute** | Custom validation logic | ⭐⭐ Medium | ✅ Via FieldReference |
| **3. Standalone ValidationRule** | Rules needing dependencies | ⭐⭐⭐ Complex | ⚠️ Limited |

---

## Approach 1: CustomValidationAttribute

**When to use:** You want to combine multiple existing Laravel validation rules into a single reusable attribute.

**Example:** `DeclinedIfIn` - combines multiple `declined_if` rules

### Implementation

```php
use Attribute;
use Spatie\LaravelData\Attributes\Validation\CustomValidationAttribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class DeclinedIfIn extends CustomValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
        protected array $values,
    ) {
        $this->field = is_string($field) ? new FieldReference($field) : $field;
    }

    /**
     * Return array of Laravel validation rule strings.
     * Laravel Data will merge these with other rules for the property.
     */
    public function getRules(ValidationPath $path): array
    {
        $fieldName = $this->field->getValue($path);

        // Generate one rule for each value
        return array_map(
            fn ($value) => "declined_if:{$fieldName},{$value}",
            $this->values
        );
    }
}
```

### Usage

```php
use App\Data\LaravelData\Attributes\Validation\DeclinedIfIn;
use App\Enums\ObjectDefinition\ObjectDefinitionColumn\ColumnTypeEnum;

class ObjectDefinitionColumnData extends Data
{
    public function __construct(
        #[Required]
        public ColumnTypeEnum $column_type,

        // This generates: declined_if:column_type,user AND declined_if:column_type,money
        #[DeclinedIfIn('column_type', [ColumnTypeEnum::USER->value, ColumnTypeEnum::MONEY->value])]
        public bool $is_computed = false,
    ) {}
}
```

### How It Works

1. Laravel Data calls `getRules($path)` to get validation rules
2. You return an array of rule strings (e.g., `["declined_if:column_type,user", "declined_if:column_type,money"]`)
3. Laravel Data merges these with other rules for the property
4. Laravel's validator processes them as normal validation rules

### Pros & Cons

✅ **Pros:**
- Simple and clean
- Leverages existing Laravel validation
- Full access to FieldReference
- Easy to test

❌ **Cons:**
- Limited to combining existing Laravel rule strings
- Cannot implement custom validation logic
- Must map to Laravel's rule syntax

---

## Approach 2: ObjectValidationAttribute

**When to use:** You need custom validation logic that doesn't map to simple Laravel rule strings.

**Example:** Custom format validation with complex regex or business logic

### Implementation

```php
use Attribute;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class CodeFormat extends ObjectValidationAttribute implements ValidationRule
{
    /**
     * Return $this because we implement ValidationRule.
     * Laravel Data will use this object as the validation rule.
     */
    public function getRule(ValidationPath $path): object|string
    {
        return $this;
    }

    /**
     * Custom validation logic.
     * This is called by Laravel's validator.
     *
     * @param string $attribute The field name being validated
     * @param mixed $value The value being validated
     * @param Closure $fail Closure to call when validation fails
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Your custom validation logic here
        if (! preg_match('/^[A-Z]{3}-[0-9]{3}$/', $value)) {
            $fail('The :attribute must be in format XXX-999 (e.g., ABC-123).');
        }
    }

    public static function keyword(): string
    {
        return 'code_format';
    }

    public static function create(string ...$parameters): static
    {
        return new static();
    }
}
```

### Usage

```php
class ProductData extends Data
{
    public function __construct(
        #[Required, StringType]
        #[CodeFormat] // Custom validation logic runs here
        public string $code,
    ) {}
}
```

### How It Works

1. Laravel Data calls `getRule($path)` to get the validation rule
2. You return `$this` (because you implement `ValidationRule`)
3. Laravel's validator calls your `validate()` method
4. You have full control over validation logic and error messages

### Pros & Cons

✅ **Pros:**
- Full control over validation logic
- Can implement complex business rules
- Custom error messages
- Still integrates cleanly with Laravel Data
- Can use FieldReference in constructor

❌ **Cons:**
- More verbose than Approach 1
- Must implement ValidationRule interface
- Limited access to other field values (see [FieldReference](#fieldreference))

---

## Approach 3: Standalone ValidationRule

**When to use:** You need dependency injection or the rule is used outside Laravel Data contexts.

**Example:** Rule that requires a service or repository

### Implementation

```php
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailDomainRule implements ValidationRule
{
    public function __construct(private string $domain) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! str_ends_with($value, "@{$this->domain}")) {
            $fail("The :attribute must be an email from {$this->domain}");
        }
    }
}
```

### Usage with Laravel Data

```php
use Spatie\LaravelData\Attributes\Validation\Rule;

class UserData extends Data
{
    public function __construct(
        #[Required]
        #[Rule(new EmailDomainRule('example.com'))] // Instantiate the rule directly
        public string $email,
    ) {}
}
```

### With Dependency Injection

```php
class UniqueColumnName implements ValidationRule
{
    public function __construct(
        private ObjectDefinitionService $service,
        private int $objectDefinitionId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->service->columnNameExists($this->objectDefinitionId, $value)) {
            $fail('The :attribute already exists in this ObjectDefinition.');
        }
    }
}

// Usage - resolve from container
#[Rule(app(UniqueColumnName::class, ['objectDefinitionId' => $this->id]))]
public string $column_name;
```

### Pros & Cons

✅ **Pros:**
- Works with Laravel's dependency injection
- Can be used outside Laravel Data
- Great for rules requiring services/repositories
- Standard Laravel validation rule

❌ **Cons:**
- Less elegant syntax
- Cannot use FieldReference helpers
- Harder to pass dynamic constructor arguments
- More setup required

---

## Understanding How Validation Works {#how-validation-works}

Laravel Data's validation happens in phases. Understanding this helps debug issues:

### Phase 1: Rule Collection

When you call `DataClass::validateAndCreate($data)`:

1. Laravel Data scans all properties for validation attributes
2. For each attribute, it calls the appropriate method:
   - `CustomValidationAttribute::getRules($path)` → returns array of rule strings
   - `ObjectValidationAttribute::getRule($path)` → returns rule object or string
   - `Rule` attribute → uses the rule object directly

### Phase 2: Rule Resolution

Laravel Data processes FieldReferences:

```php
// Your code:
#[RequiredIf('status', 'active')]
public ?string $name;

// Laravel Data resolves to:
// "required_if:status,active" (for root-level field)
// OR
// "required_if:parent.status,active" (for nested field)
```

### Phase 3: Laravel Validation

Laravel Data passes all resolved rules to Laravel's standard validator:

```php
// What Laravel Data builds internally:
$rules = [
    'status' => ['required', 'string'],
    'name' => ['nullable', 'string', 'required_if:status,active'],
];

$validator = Validator::make($data, $rules);
```

### Phase 4: Validation Execution

Laravel's validator runs each rule:

1. Built-in rules (required, string, etc.) execute first
2. Custom ValidationRule objects call your `validate()` method
3. If any rule fails, a ValidationException is thrown

---

## FieldReference: The Missing Manual {#fieldreference}

**The most confusing part of Laravel Data validation.** Here's how it actually works:

### What is FieldReference?

`FieldReference` tells Laravel Data which field to reference in a validation rule. It's critical for rules like `required_if`, `declined_if`, `same`, etc.

### Two Modes: Relative vs Root

#### Relative Reference (Default)

References fields **relative to the current property**.

```php
class ChildData extends Data
{
    public function __construct(
        public string $status,

        // FieldReference('status') looks for a sibling field
        #[RequiredIf('status', 'active')]
        public ?string $value,
    ) {}
}

class ParentData extends Data
{
    public function __construct(
        public ChildData $child,
    ) {}
}

// Validation rules generated:
// 'child.status' => ['required', 'string']
// 'child.value' => ['nullable', 'string', 'required_if:child.status,active']
//                                                       ^^^^^^^^^^^^
//                                                  Relative to child!
```

#### Root Reference (fromRoot: true)

References fields **from the root data object**.

```php
class ChildData extends Data
{
    public function __construct(
        // FieldReference with fromRoot looks at root-level fields
        #[RequiredIf(new FieldReference('root_status', fromRoot: true), 'active')]
        public ?string $value,
    ) {}
}

class ParentData extends Data
{
    public function __construct(
        public string $root_status,
        public ChildData $child,
    ) {}
}

// Validation rules generated:
// 'root_status' => ['required', 'string']
// 'child.value' => ['nullable', 'string', 'required_if:root_status,active']
//                                                       ^^^^^^^^^^^
//                                              References root field!
```

### When to Use Each

| Scenario | Use | Example |
|----------|-----|---------|
| Field in same data class | Relative (default) | `#[RequiredIf('status', 'active')]` |
| Field in parent class | Root reference | `#[RequiredIf(new FieldReference('parent_field', fromRoot: true), 'value')]` |
| Field in sibling nested class | ❌ Not supported | Use server-side validation instead |

### How ValidationPath Works

`ValidationPath` tracks where you are in the nested data structure:

```php
// Root level:
ValidationPath::create() // ""

// Nested:
$path->property('child') // "child"
$path->property('items')->property(0) // "items.0"
```

FieldReference uses this path to resolve field names:

```php
$field = new FieldReference('status');
$field->getValue($path); // Returns the full dotted path to the field

// At root: "status"
// In child: "child.status"
// In collection: "items.0.status"
```

---

## Working Examples {#working-examples}

### Example 1: Multiple Conditional Requirements

```php
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RequiredIfIn extends CustomValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
        protected array $values,
    ) {
        $this->field = is_string($field) ? new FieldReference($field) : $field;
    }

    public function getRules(ValidationPath $path): array
    {
        $fieldName = $this->field->getValue($path);
        return array_map(
            fn ($value) => "required_if:{$fieldName},{$value}",
            $this->values
        );
    }
}

// Usage - MULTIPLE attributes on same property (note IS_REPEATABLE):
class OrderData extends Data
{
    public function __construct(
        public string $type,
        public string $status,

        #[RequiredIfIn('type', ['user', 'admin'])]
        #[RequiredIfIn('status', ['active', 'pending'])]
        public ?string $name = null, // Required if type is user/admin OR status is active/pending
    ) {}
}
```

### Example 2: Custom Business Logic

```php
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ValidProductCode extends ObjectValidationAttribute implements ValidationRule
{
    public function getRule(ValidationPath $path): object|string
    {
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Complex business logic
        if (! $this->isValidChecksum($value)) {
            $fail('The :attribute has an invalid checksum.');
        }
    }

    private function isValidChecksum(string $code): bool
    {
        // Your complex validation logic
        return true;
    }

    public static function keyword(): string
    {
        return 'valid_product_code';
    }

    public static function create(string ...$parameters): static
    {
        return new static();
    }
}
```

### Example 3: Accessing Other Fields

**🚨 CRITICAL: Never use `request()` in validation attributes!**

See `http-context-dependencies.md` for the full explanation. In short: `request()` returns null outside HTTP context (tests, CLI, queues), so validation attributes using it will silently fail.

**For cross-field validation, use one of these approaches:**
1. `CustomValidationAttribute` with `FieldReference` (Approach 1) - generates Laravel rule strings
2. Laravel's built-in rules (`prohibited_if`, `required_without`, `same`, etc.)
3. The Data class's `rules()` method for complex cross-field logic

---

## Common Pitfalls {#common-pitfalls}

### 1. Forgetting `IS_REPEATABLE`

**Problem:** You want to use the same attribute multiple times on a property.

```php
// ❌ FAILS - Attribute must not be repeated
class OrderData extends Data
{
    public function __construct(
        #[RequiredIf('type', 'user')]
        #[RequiredIf('status', 'active')] // ERROR!
        public ?string $name,
    ) {}
}
```

**Solution:** Add `IS_REPEATABLE` to your attribute:

```php
// ✅ WORKS
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RequiredIf extends CustomValidationAttribute { ... }
```

### 2. Incorrect FieldReference Mode

**Problem:** Using relative reference when you need root reference.

```php
// ❌ WRONG - Looks for child.parent_status (doesn't exist)
class ChildData extends Data
{
    public function __construct(
        #[RequiredIf('parent_status', 'active')]
        public ?string $value,
    ) {}
}
```

**Solution:** Use `fromRoot: true`:

```php
// ✅ CORRECT - Looks for root-level parent_status
class ChildData extends Data
{
    public function __construct(
        #[RequiredIf(new FieldReference('parent_status', fromRoot: true), 'active')]
        public ?string $value,
    ) {}
}
```

### 3. Never Use `request()` in Validation Attributes

**🚨 CRITICAL: This is a common mistake that causes silent validation failures.**

**Problem:** `request()->input()` returns `null` outside HTTP context - in tests, CLI, queues, and even some validation scenarios.

```php
// ❌ NEVER DO THIS - validation silently fails in tests!
public function validate(string $attribute, mixed $value, Closure $fail): void
{
    $otherValue = request()->input('other_field'); // Returns null in tests!

    // This condition is always false when request() returns null
    if ($value !== null && $otherValue !== null) {
        $fail('Both fields cannot be set');
    }
}
```

**Solution:** Use Approach 1 (CustomValidationAttribute with FieldReference) to generate Laravel rule strings that handle context correctly:

```php
// ✅ CORRECT - Use FieldReference + Laravel's built-in rules
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
        // Laravel's validator handles this correctly in all contexts
        return ["prohibited_if:{$fieldName},!=,"];
    }
}
```

**See:** `http-context-dependencies.md` for the full architectural explanation of why HTTP context helpers should never be used outside controllers.

### 4. Not Implementing Required Methods

**Problem:** Forgetting `keyword()` or `create()` methods.

```php
// ❌ INCOMPLETE
class MyRule extends ObjectValidationAttribute implements ValidationRule
{
    public function getRule(ValidationPath $path): object|string { return $this; }
    public function validate(...) { ... }
    // Missing keyword() and create()!
}
```

**Solution:** Always implement all required methods:

```php
// ✅ COMPLETE
public static function keyword(): string
{
    return 'my_rule';
}

public static function create(string ...$parameters): static
{
    return new static(...$parameters);
}
```

---

## Decision Tree {#decision-tree}

```
Need custom validation?
│
├─ Combining existing Laravel rules?
│  └─ ✅ Use Approach 1: CustomValidationAttribute
│
├─ Custom logic, no service dependencies?
│  └─ ✅ Use Approach 2: ObjectValidationAttribute
│
├─ Need dependency injection?
│  └─ ✅ Use Approach 3: Standalone ValidationRule
│
└─ Need to reference other fields?
   ├─ Same class? → Use FieldReference (relative)
   ├─ Parent class? → Use FieldReference(fromRoot: true)
   └─ Sibling class? → ❌ Not supported, use server-side validation
```

---

## Summary

**The three approaches:**

1. **CustomValidationAttribute** - Returns array of Laravel rule strings
2. **ObjectValidationAttribute** - Implements custom `validate()` method
3. **Standalone ValidationRule** - Pure Laravel rule with dependency injection

**Key concepts:**

- **FieldReference** - References other fields (relative or from root)
- **ValidationPath** - Tracks position in nested data
- **IS_REPEATABLE** - Required for using attribute multiple times

**When in doubt:** Start with Approach 1 (CustomValidationAttribute) - it's the simplest and covers 80% of use cases.

---

## See Also

- `laravel-data.md` - General Laravel Data conventions
- Working examples: `tests/Unit/Data/LaravelData/CustomValidationExamplesTest.php`
- DeclinedIfIn implementation: `app/Data/LaravelData/Attributes/Validation/DeclinedIfIn.php`

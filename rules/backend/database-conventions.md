---
paths: database/migrations/*.php
---

# Database Conventions

This document provides database schema design and migration conventions for Laravel applications.

## Column Comments (Critical)

**ALWAYS add `->comment()` to every column.**

Comments are essential for:
- Understanding column purpose without reading code
- Database documentation
- Helping new developers understand the schema
- Maintaining clarity as the application grows

```php
// ✅ CORRECT - All columns have comments
Schema::create('orders', function (Blueprint $table) {
    $table->id()->comment('Primary key');
    $table->foreignId('customer_id')->constrained()->comment('Reference to customer who placed the order');
    $table->string('order_number')->unique()->comment('Unique order identifier shown to customer');
    $table->string('status')->comment('Order status: pending, processing, completed, cancelled');
    $table->decimal('total', 10, 2)->comment('Total order amount in base currency');
    $table->timestamp('completed_at')->nullable()->comment('When the order was marked as completed');
    $table->timestamps();
});

// ❌ WRONG - Missing comments
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained();
    $table->string('order_number');
    $table->string('status');
    $table->decimal('total', 10, 2);
    $table->timestamps();
});
```

### Comment Guidelines

**Good comments:**
- Explain the purpose, not just restate the name
- Mention valid values for enum-like columns
- Indicate units for numeric columns (currency, percentage, etc.)
- Note relationships for foreign keys

```php
// ✅ GOOD COMMENTS
$table->string('status')->comment('Order status: pending, processing, completed, cancelled');
$table->decimal('discount_percentage', 5, 2)->comment('Discount as percentage (0-100)');
$table->foreignId('assigned_to')->nullable()->constrained('users')->comment('User assigned to handle this order, null if unassigned');

// ❌ BAD COMMENTS
$table->string('status')->comment('Status');  // Too vague
$table->decimal('discount')->comment('Discount');  // Missing units
```

## Enum Columns (Critical)

**ALWAYS use string columns for enum values, NEVER use database enum types.**

Maintain enum values in PHP using backed enums, not in the database schema.

### Why String Columns for Enums?

1. **Schema Flexibility**: Add new enum values without altering the database
2. **Migration Independence**: No need for database migrations when adding values
3. **Easier Rollbacks**: No schema changes to revert
4. **Type Safety**: PHP enums provide compile-time checking
5. **Database Portability**: String columns work across all database engines

### The Pattern

**Step 1:** Create a PHP backed enum:

```php
<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}
```

**Step 2:** Use string column in migration with enum values in comment:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id()->comment('Primary key');
    $table->string('status')
        ->default('pending')
        ->comment('Order status: pending, processing, completed, cancelled, refunded');
    $table->timestamps();
});
```

**Step 3:** Cast to enum in model:

```php
use App\Enums\OrderStatus;

class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
```

### ✅ CORRECT Example

```php
// Migration
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id()->comment('Primary key');
    $table->foreignId('user_id')->constrained()->comment('User who owns this subscription');
    $table->string('plan')
        ->comment('Subscription plan: free, basic, pro, enterprise');
    $table->string('status')
        ->comment('Subscription status: active, cancelled, expired, paused');
    $table->timestamp('expires_at')
        ->nullable()
        ->comment('When the subscription expires, null for lifetime plans');
    $table->timestamps();
});

// Enum
enum SubscriptionPlan: string
{
    case FREE = 'free';
    case BASIC = 'basic';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';
}

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case PAUSED = 'paused';
}

// Model
class Subscription extends Model
{
    protected $casts = [
        'plan' => SubscriptionPlan::class,
        'status' => SubscriptionStatus::class,
        'expires_at' => 'datetime',
    ];
}
```

### ❌ WRONG - Using Database Enum

```php
// DON'T DO THIS
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->enum('status', ['pending', 'processing', 'completed']); // WRONG!
    $table->timestamps();
});
```

**Problems with database enums:**
- Requires migration to add new values
- Harder to maintain
- Less portable across databases
- Can't easily see valid values in code

## Additional Column Conventions

### Foreign Keys

```php
// ✅ CORRECT - Clear comment explaining the relationship
$table->foreignId('author_id')
    ->constrained('users')
    ->comment('User who authored this post');

$table->foreignId('category_id')
    ->constrained()
    ->cascadeOnDelete()
    ->comment('Post category, deletes post when category is deleted');

// With nullable foreign key
$table->foreignId('approved_by')
    ->nullable()
    ->constrained('users')
    ->comment('User who approved this post, null if pending approval');
```

### Timestamps

```php
// Standard timestamps (created_at, updated_at)
$table->timestamps();

// Custom timestamps - always add comments
$table->timestamp('published_at')
    ->nullable()
    ->comment('When the post was published, null for drafts');

$table->timestamp('deleted_at')
    ->nullable()
    ->comment('Soft delete timestamp');

$table->timestamp('expires_at')
    ->nullable()
    ->comment('When the coupon expires and becomes invalid');
```

### JSON Columns

```php
// ✅ CORRECT - Comment explains the structure
$table->json('metadata')
    ->nullable()
    ->comment('Additional metadata, see ProfileData class for structure');

$table->json('settings')
    ->comment('User preferences, see UserSettingsData class for structure');

// In model - Cast to Data class (see Data Classes convention)
use App\Data\User\ProfileData;

protected $casts = [
    'metadata' => ProfileData::class,
];
```

### Boolean Columns

```php
// ✅ CORRECT - Comment explains true/false meaning
$table->boolean('is_active')
    ->default(true)
    ->comment('Whether the user account is active (true) or disabled (false)');

$table->boolean('is_published')
    ->default(false)
    ->comment('Whether the post is visible to public (true) or draft (false)');

$table->boolean('email_verified')
    ->default(false)
    ->comment('Whether user has verified their email address');
```

### Decimal/Money Columns

```php
// ✅ CORRECT - Specify precision and comment with currency/unit
$table->decimal('price', 10, 2)
    ->comment('Product price in base currency (2 decimal places)');

$table->decimal('tax_rate', 5, 2)
    ->comment('Tax rate as percentage (e.g., 7.50 for 7.5%)');

$table->decimal('discount_amount', 10, 2)
    ->nullable()
    ->comment('Discount amount in base currency, null if no discount');
```

### Unique Constraints

```php
// ✅ CORRECT
$table->string('email')
    ->unique()
    ->comment('User email address, must be unique across system');

$table->string('slug')
    ->unique()
    ->comment('URL-friendly identifier, unique per post');

// Composite unique
$table->string('code')
    ->comment('Coupon code');
$table->foreignId('campaign_id')
    ->constrained()
    ->comment('Marketing campaign this coupon belongs to');
$table->unique(['code', 'campaign_id'], 'unique_code_per_campaign');
```

## Index Conventions

Add indexes for commonly queried columns:

```php
// ✅ CORRECT - Add indexes for performance
Schema::create('posts', function (Blueprint $table) {
    $table->id()->comment('Primary key');
    $table->foreignId('author_id')->constrained('users')->comment('Post author');
    $table->string('status')->index()->comment('Publication status: draft, published, archived');
    $table->timestamp('published_at')->nullable()->index()->comment('Publication date, null for drafts');
    $table->timestamps();

    // Composite index for common query patterns
    $table->index(['status', 'published_at']);
});
```

## Migration Best Practices

### Naming Migrations

```php
// ✅ GOOD - Descriptive migration names
2024_01_15_100000_create_orders_table.php
2024_01_15_110000_add_status_to_orders_table.php
2024_01_15_120000_add_indexes_to_posts_table.php

// ❌ BAD
2024_01_15_100000_orders.php
2024_01_15_110000_update_table.php
```

### Adding Columns

```php
// ✅ CORRECT - New column with comment and proper placement
Schema::table('users', function (Blueprint $table) {
    $table->string('phone_number')
        ->nullable()
        ->after('email')
        ->comment('User phone number for SMS notifications');
});
```

### Modifying Columns

```php
// ✅ CORRECT - Document the change
Schema::table('orders', function (Blueprint $table) {
    $table->decimal('total', 12, 2)
        ->change()
        ->comment('Increased precision to support larger order totals');
});
```

## Complete Migration Example

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // Primary key
            $table->id()->comment('Primary key');

            // Foreign keys
            $table->foreignId('customer_id')
                ->constrained()
                ->comment('Customer who placed the order');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->comment('Staff member assigned to fulfill order, null if unassigned');

            // Order details
            $table->string('order_number')
                ->unique()
                ->comment('Unique order number displayed to customer (e.g., ORD-2024-001)');

            $table->string('status')
                ->default('pending')
                ->index()
                ->comment('Order status: pending, processing, completed, cancelled, refunded');

            // Pricing
            $table->decimal('subtotal', 10, 2)
                ->comment('Sum of all item prices before tax and discounts');

            $table->decimal('tax_amount', 10, 2)
                ->comment('Total tax amount');

            $table->decimal('discount_amount', 10, 2)
                ->default(0)
                ->comment('Total discount applied');

            $table->decimal('total', 10, 2)
                ->comment('Final order total including tax and discounts');

            // Metadata
            $table->json('shipping_address')
                ->comment('Shipping address details, see ShippingAddressData class');

            $table->text('notes')
                ->nullable()
                ->comment('Internal notes about the order, not visible to customer');

            // Timestamps
            $table->timestamp('confirmed_at')
                ->nullable()
                ->comment('When the order was confirmed by customer');

            $table->timestamp('completed_at')
                ->nullable()
                ->index()
                ->comment('When the order was marked as completed');

            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for cancelled orders');

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

## Checklist for New Migrations

Before creating a migration:

- [ ] All columns have `->comment()` describing their purpose
- [ ] Enum-like columns use `string` type, not database enum
- [ ] Enum values are listed in comment
- [ ] Comments mention valid values, units, or ranges where applicable
- [ ] Foreign keys clearly explain the relationship
- [ ] Appropriate indexes added for commonly queried columns
- [ ] Decimal columns specify precision (e.g., `decimal('price', 10, 2)`)
- [ ] Nullable columns have logical default values or are intentionally nullable
- [ ] Boolean columns explain what true/false means
- [ ] JSON columns reference the corresponding Data class
- [ ] Migration name is descriptive

## Final Reminder

**Two Critical Rules:**

1. **ALWAYS add `->comment()` to every column** - Future developers (including you) will thank you
2. **ALWAYS use string columns for enums** - Maintain enum values in PHP, not database schema

---
paths: database/migrations/**/*.php
---

# Migration Workflow: Unpublished vs Published Features

> **Unpublished features: modify existing migrations. Published features: create new corrective migrations.**

## The Rule

The migration strategy depends on whether the feature has been published to production.

| Feature Status | Migration Strategy | Reasoning |
|----------------|-------------------|-----------|
| **Unpublished** (dev branch only) | Modify or replace existing migrations | No production data to protect; keep clean history |
| **Published** (merged to main/production) | Create new corrective migrations | Protect production data; maintain migration order |

## Unpublished Features (This Branch Only)

If the feature **has NOT been merged** to the main branch or deployed to production:

### ✅ CORRECT - Modify/Replace Migrations

```bash
# Scenario: You created a migration, then realized it needs changes

# Step 1: Delete the old migration file
rm database/migrations/2026_01_05_170000_add_status_to_orders.php

# Step 2: Create a new, correct migration
php artisan make:migration add_fulfillment_status_to_orders

# Step 3: Implement the correct schema
# (No need for backwards compatibility since it was never deployed)
```

**Why this approach:**
- No production data exists yet
- Keeps migration history clean
- No unnecessary "correction" migrations
- Easier to understand for future developers

### ❌ WRONG - Creating Corrective Migrations for Unpublished Code

```bash
# DON'T DO THIS for unpublished features
database/migrations/
  2026_01_05_170000_add_status_to_orders.php           # Original
  2026_01_05_180000_fix_status_field_on_orders.php     # Correction - UNNECESSARY
```

**Problem:** You end up with migration clutter for code that never reached production.

## Published Features (Merged/Deployed)

If the feature **HAS been merged** to main or deployed to production:

### ✅ CORRECT - Create New Corrective Migration

```bash
# Scenario: Feature is live, but needs schema changes

# Step 1: Create a new migration (keep the old one)
php artisan make:migration update_author_to_polymorphic_on_posts

# Step 2: Implement migration that handles existing data
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        // Add new columns
        $table->unsignedBigInteger('author_id')->nullable();
        $table->string('author_type')->nullable();

        // Migrate existing data
        DB::statement('
            UPDATE posts
            SET author_id = user_id,
                author_type = "App\\Models\\User"
            WHERE user_id IS NOT NULL
        ');

        // Retire the old column instead of dropping it (see "Retire, Don't Erase" below)
        $table->dropForeign(['user_id']);
        $table->renameColumn('user_id', 'retired_user_id');
    });
}
```

**Why this approach:**
- Protects production data
- Maintains proper migration order
- Can be rolled back safely
- Documents the evolution of the schema

### ❌ WRONG - Modifying Published Migrations

```php
// DON'T DO THIS if the migration has been deployed
// database/migrations/2026_01_05_170000_add_user_id_to_posts.php

public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        // Changing this after it's been run in production will cause issues
        $table->foreignId('user_id'); // Original
        // Someone might edit this to polymorphic - BAD!
    });
}
```

**Problem:** Production databases already ran the old version. Your local change won't apply to production.

## How to Tell if a Feature is Published

### Unpublished (Safe to Modify Migrations)

- Feature branch not yet merged to main
- Migration never ran on staging/production
- You're the only one working on this branch
- No other developers have pulled your branch

### Published (Use Corrective Migrations)

- Feature merged to main/develop branch
- Migration ran on staging or production
- Other developers have the migration
- Feature is deployed anywhere outside your local machine

## Decision Tree

```
Is the migration file in the main/develop branch?
├─ YES → Feature is published
│   └─ Create new corrective migration
│
└─ NO → Feature is unpublished
    ├─ Has anyone else pulled this branch?
    │   ├─ YES → Coordinate before changing migrations
    │   └─ NO → Safe to modify/replace migrations
    │
    └─ Has this run on staging/production?
        ├─ YES → Feature is published (use corrective migrations)
        └─ NO → Safe to modify/replace migrations
```

## Communication is Key

When in doubt:

1. **Check git history:** `git log --all --oneline -- database/migrations/your_migration.php`
2. **Check branches:** `git branch -r --contains <commit-hash-of-migration>`
3. **Ask the team:** "Has this migration run anywhere besides my local machine?"

## SQLite Compatibility Note

For unpublished features, you don't need SQLite-specific workarounds:

```php
// ❌ UNNECESSARY for unpublished features
if (DB::getDriverName() === 'sqlite') {
    // Complex workaround because we're trying to fix a published migration
}

// ✅ CORRECT for unpublished - just write the right migration from the start
Schema::table('orders', function (Blueprint $table) {
    $table->unsignedBigInteger('customer_id')->nullable();
    $table->string('status')->default('pending');
});
```

If you find yourself writing database driver checks for "corrective" migrations on an unpublished feature, stop and delete the old migration instead.

## Retire, Don't Erase: Every Published Migration Keeps a Way Back

> **Don't drop a column or table that holds data — retire it. Before changing or deleting existing rows, back up what you change. `down()` must bring the data back, not just the schema.**

The principle and its reasoning: [Retire, Don't Erase](https://github.com/RasmusGodske/design-principles/blob/main/skills/design-principles/retire-dont-erase/README.md). This section is how to do it in Laravel. It applies to every published migration that drops a column or table, deletes rows, or changes existing data. Unpublished migrations can simply be fixed, and recalculable data (caches, derived columns) needs no backup.

A reversible migration is also quicker to review: the reviewer checks that `down()` works instead of tracing every consequence of losing the data.

### Names

| Thing | Name |
|---|---|
| Retired column | `retired_{column}`, e.g. `retired_delivery_method` |
| Retired table | `retired_{table}`, e.g. `retired_gift_wraps` |
| Backup of one changed table | `migration_backup_{source_table}_{purpose}`, e.g. `migration_backup_orders_status_spelling` |

- One backup table **per changed table**, created by the migration that needs it.
- Names stay within **63 characters**; Postgres silently truncates longer identifiers (MySQL rejects them above 64).

### Every retired thing and every backup table gets a comment

Name the migration file that created it and say why, so a developer finding it later can trace it back even if migration files are ever pruned:

```php
$table->comment('Backup made by 2024_01_15_100000_normalize_order_status_spelling: '
    .'status of each order before "canceled" was respelled "cancelled". '
    .'Its down() restores from here.');
```

`$table->comment()` comments a table, `->comment()` on a column comments the column. Read it back with `\d+ {table}` in psql.

### Retiring a column

```php
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->renameColumn('delivery_method', 'retired_delivery_method');
    });

    Schema::table('orders', function (Blueprint $table) {
        $table->string('retired_delivery_method')
            ->nullable()
            ->comment('Retired by 2024_01_15_100000_retire_delivery_method_on_orders: every order ships the same way, so nothing reads this. Kept so down() can bring it back.')
            ->change();
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('retired_delivery_method')
            ->nullable(false)
            ->default('standard')
            ->comment('How the order is shipped: standard or express')
            ->change();
    });

    Schema::table('orders', function (Blueprint $table) {
        $table->renameColumn('retired_delivery_method', 'delivery_method');
    });
}
```

- **Make it nullable.** A retired `NOT NULL` column without a default breaks every new insert, because no code fills it any more.
- **`->change()` restates the whole column.** Attributes left out (default, nullability, comment) are dropped, so `down()` spells out the original definition in full.
- **Remove it from the model** (casts, fillable, `@property`) in the same change. Nothing reads a retired column.

Retiring a table is `Schema::rename('x', 'retired_x')` plus a table comment; `down()` renames it back.

### Backing up rows you change

The backup table has a foreign key to the changed row and, per changed column, `original_{column}` and `rewritten_{column}` of the same type as the source column:

```php
private const BACKUP = 'migration_backup_orders_status_spelling';

public function up(): void
{
    Schema::create(self::BACKUP, function (Blueprint $table) {
        $table->comment('Backup made by …: … Its down() restores from here.');
        $table->id()->comment('Primary key');
        $table->foreignId('order_id')->constrained('orders')->comment('Order whose status this row preserves');
        $table->string('original_status')->comment('orders.status before this migration');
        $table->string('rewritten_status')->comment('orders.status as this migration wrote it; down() restores only while it still equals this');
        // … same pair for every other changed column
        $table->timestamps();
    });

    // 1. Back up exactly the rows and columns about to change.
    // 2. Change them.
    // 3. Verify: every backed-up row now equals its rewritten_* values, else throw.
}

public function down(): void
{
    // 1. Refuse if any row no longer equals its rewritten_* values, naming those rows.
    // 2. Restore original_*.
    // 3. Schema::drop(self::BACKUP).
}
```

- **Query builder or SQL, never models.** Model events would fire on every row (observers, syncs, recalculations), and the model may have changed shape by the time the migration runs elsewhere.
- **It is all-or-nothing on Postgres.** Laravel runs each migration in a transaction (`$withinTransaction` defaults to `true`), so a failed check undoes the backup table and the rewrite together. MySQL commits DDL implicitly, so there a failed rewrite leaves the backup table behind.
- **The foreign key protects the backup:** a backed-up row cannot be hard-deleted while its backup exists.

For **deleted rows**, the backup table copies every column of the row (without the source's unique constraints and foreign keys) and has no `rewritten_` columns; `down()` inserts them back.

### Erasing later

Dropping a `retired_` column or table, or a `migration_backup_` table, is its own later migration, written once someone has decided it is no longer needed. Its docblock says plainly that its `down()` cannot bring the data back.

### ❌ WRONG — the schema comes back, the data doesn't

```php
public function up(): void
{
    Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('delivery_method'));
}

public function down(): void
{
    // Every order comes back as 'standard'; what each one actually had is gone.
    Schema::table('orders', fn (Blueprint $table) => $table->string('delivery_method')->default('standard'));
}
```

## Summary

**Unpublished feature?**
- ✅ Delete old migration, create new one
- ✅ No need for data migration logic
- ✅ Keep it clean and simple

**Published feature?**
- ✅ Keep old migration
- ✅ Create new corrective migration
- ✅ Handle existing data carefully
- ✅ Test rollback behavior
- ✅ Retire columns and tables instead of dropping them; back up rows before changing or deleting them

When in doubt, ask: "Has this migration touched a database that isn't on my local machine?" If yes, it's published.

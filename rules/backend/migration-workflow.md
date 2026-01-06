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

        // Drop old column
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
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

When in doubt, ask: "Has this migration touched a database that isn't on my local machine?" If yes, it's published.

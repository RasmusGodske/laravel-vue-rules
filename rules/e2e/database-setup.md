---
paths: e2e/**/*.ts
---

# E2E Database Setup

E2E tests use an **isolated SQLite database** that's separate from your development database.

## Why SQLite?

| Aspect | SQLite | PostgreSQL |
|--------|--------|------------|
| Speed | Faster (no network) | Slower |
| Isolation | Per-file, easy reset | Shared server |
| Setup | Just touch a file | Needs container |
| Cleanup | Delete file | Truncate/drop |

This matches how PHPUnit tests work - same `testing` connection, same factories.

## Required Files

### 1. Environment File (`.env.e2e`)

Create `.env.e2e` in project root:

```env
# E2E Testing Environment
APP_ENV=local
APP_DEBUG=true
APP_PORT=8081
APP_URL=http://localhost:8081

# Use SQLite for isolation
DB_CONNECTION=testing

# Simple drivers (no external services)
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Disable debug tools (prevent console errors)
TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false

# Performance
XDEBUG_MODE=off
LOG_LEVEL=warning
```

**Key settings:**
- `APP_ENV=local` - Required for testing routes to be registered
- `DB_CONNECTION=testing` - Uses SQLite connection from `config/database.php`
- `APP_PORT=8081` - Different from dev server (8080) to avoid conflicts

### 2. Database Connection (`config/database.php`)

Ensure you have a `testing` connection:

```php
'testing' => [
    'driver' => 'sqlite',
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
],
```

### 3. Server Script (`e2e/scripts/e2e-server.sh`)

Create a bash script to start the E2E server:

```bash
#!/bin/bash
# E2E Test Server Startup Script
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"

cd "$PROJECT_DIR"

echo "=== E2E Server Setup ==="

# 1. Build frontend assets
echo "Building frontend assets..."
npm run build

# 2. Load E2E environment (export for child processes)
echo "Loading E2E environment..."
set -a
source .env.e2e
set +a

# 3. Set absolute path for SQLite (required for artisan serve workers)
export DB_DATABASE="$PROJECT_DIR/database/e2e.sqlite"

# 4. Create database file if needed
touch "$DB_DATABASE"

# 5. Fresh migrations
echo "Running migrations..."
php artisan migrate:fresh --force

# 6. Run any required seeders (project-specific)
# echo "Seeding data..."
# php artisan db:seed --class="YourSeeder" --force

# 7. Start server
echo "Starting server on port 8081..."
php artisan serve --host=0.0.0.0 --port=8081
```

**Make it executable:**
```bash
chmod +x e2e/scripts/e2e-server.sh
```

### 4. Playwright Config (`playwright.config.ts`)

Configure Playwright to use the script:

```typescript
export default defineConfig({
  // ... other config ...

  webServer: {
    command: './e2e/scripts/e2e-server.sh',
    url: 'http://localhost:8081',
    reuseExistingServer: !process.env.CI && false,
    timeout: 120 * 1000,  // 2 minutes for build + migrate
    stdout: 'ignore',
    stderr: 'pipe',
  },
})
```

## How It Works

```
npm run e2e
    │
    ├── Playwright starts
    │
    ├── e2e-server.sh runs:
    │   ├── npm run build (compile assets)
    │   ├── source .env.e2e (load environment)
    │   ├── touch database/e2e.sqlite
    │   ├── php artisan migrate:fresh
    │   └── php artisan serve :8081
    │
    ├── Tests run against :8081
    │   ├── Each test creates its own data via API
    │   └── Each test cleans up after itself
    │
    └── Server stops when tests complete
```

## Important Notes

### Absolute Path for SQLite

The SQLite path **must be absolute** because `artisan serve` spawns worker processes that need to find the database:

```bash
# Wrong - relative path won't work for workers
export DB_DATABASE="database/e2e.sqlite"

# Correct - absolute path
export DB_DATABASE="$PROJECT_DIR/database/e2e.sqlite"
```

### Environment Export

Use `set -a` to export all sourced variables:

```bash
set -a           # Mark all variables for export
source .env.e2e  # Source the file
set +a           # Stop marking for export
```

Without this, child processes (like artisan serve workers) won't see the environment variables.

### Project-Specific Seeders

If your app requires certain data to exist (like user groups, roles, etc.), add seeder calls to the script:

```bash
# After migrate:fresh
php artisan db:seed --class="YourRequiredSeeder" --force
```

Check `e2e/docs/` for project-specific seeder requirements.

## Troubleshooting

### "Database does not exist"

The script should create it. Manual fix:
```bash
touch database/e2e.sqlite
```

### "Table doesn't exist"

Migrations didn't run. Debug:
```bash
source .env.e2e
export DB_DATABASE="/absolute/path/to/database/e2e.sqlite"
php artisan migrate:fresh --force
```

### Wrong database being used

Add debug output to script:
```bash
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_DATABASE: $DB_DATABASE"
```

### Testing routes return 404

Check `APP_ENV=local` is set. Testing routes only register in local/testing environments.

## File Locations

| File | Purpose |
|------|---------|
| `.env.e2e` | E2E environment configuration |
| `database/e2e.sqlite` | SQLite database (gitignored) |
| `e2e/scripts/e2e-server.sh` | Server startup script |
| `config/database.php` | Database connections (testing) |

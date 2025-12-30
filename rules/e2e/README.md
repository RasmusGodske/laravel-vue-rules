---
paths: e2e/**/*.ts
---

# E2E Testing Rules (Playwright)

These rules are **automatically loaded** when working on Playwright E2E test files.

**IMPORTANT:** For project-specific details, read:
- `e2e/README.md` - Project-specific setup and configuration
- `e2e/docs/` - Detailed documentation on architecture, test data, and patterns

## Available Rules

| Rule File | Loaded For | Covers |
|-----------|------------|--------|
| `database-setup.md` | `e2e/**/*.ts` | SQLite isolation, .env.e2e, server script |
| `smoke-tests.md` | `e2e/tests/**/*.ts` | Console error collection, smoke test pattern |
| `test-conventions.md` | `e2e/tests/**/*.ts` | Test structure, fixtures, assertions |
| `test-data.md` | `e2e/**/*.ts` | Test data management via API |
| `page-objects.md` | `e2e/pages/**/*.ts` | Page Object Model pattern |

## Directory Structure

```
e2e/
├── docs/                        # Project-specific documentation
│   ├── architecture.md          # Design decisions and philosophy
│   ├── database-isolation.md    # Database setup details
│   ├── test-data-management.md  # Test data API details
│   └── writing-tests.md         # How to write tests
├── fixtures/                    # Custom Playwright fixtures
│   ├── base.fixture.ts          # Extended test with authenticatedPage
│   ├── test-data.ts             # Helper for API-based test data
│   └── console-errors.ts        # Console error collection utility
├── pages/                       # Page Object Models (mirrors resources/js/Pages/)
├── scripts/
│   └── e2e-server.sh            # Server startup script
├── tests/                       # Test files (mirrors resources/js/Pages/)
└── README.md                    # Project-specific quick start
```

## Key Concepts

### 1. Isolated Database

E2E tests use a **separate SQLite database**, not your development database:
- Fresh database per test run (`migrate:fresh`)
- No interference with dev data
- See `database-setup.md` for details

### 2. Test Data via API

Tests create data through `/e2e/*` API endpoints (local env only):
- Uses Laravel factories
- Automatic cleanup after tests
- See `test-data.md` for patterns

### 3. Custom Fixtures

| Fixture | Purpose |
|---------|---------|
| `page` | Raw Playwright page (no auth) |
| `authenticatedPage` | Logged-in page with test data |
| `testData` | Test data without login |
| `testDataHelper` | Manual test data control |

### 4. Smoke Tests

Every page should have smoke tests that verify:
- Page loads without console errors
- Basic content is visible
- See `smoke-tests.md` for pattern

## Quick Example

```typescript
import { test, expect } from '../../../../fixtures/base.fixture'
import { createConsoleErrorCollector } from '../../../../fixtures/console-errors'

test.describe('Dashboard', () => {
  test('page loads without console errors', async ({ authenticatedPage }) => {
    const { page } = authenticatedPage
    const errorCollector = createConsoleErrorCollector(page)

    await page.goto('/app/dashboard')
    await page.waitForLoadState('networkidle')

    expect(errorCollector.getErrors()).toEqual([])
  })
})
```

## Environment Configuration

E2E tests use `.env.e2e` for isolated settings:

| Setting | Value | Purpose |
|---------|-------|---------|
| `APP_ENV` | local | Enable testing routes |
| `APP_PORT` | 8081 | Avoid conflict with dev server (8080) |
| `DB_CONNECTION` | testing | Use SQLite database |
| `DEBUGBAR_ENABLED` | false | Prevent console noise |
| `TELESCOPE_ENABLED` | false | Reduce overhead |
| `QUEUE_CONNECTION` | sync | Immediate processing |

## Running Tests

```bash
npm run e2e              # Run all tests
npm run e2e:ui           # Interactive UI mode
npm run e2e:headed       # See browser
npm run e2e:debug        # Debug mode
```

## Key Conventions

| Aspect | Convention |
|--------|------------|
| **Test directories** | Mirror `resources/js/Pages/` with PascalCase |
| **Test files** | kebab-case, feature-focused names |
| **Smoke tests** | Every page needs `smoke.spec.ts` |
| **Auth tests** | Use `authenticatedPage` fixture |
| **No-auth tests** | Use `page` fixture directly |
| **Port** | 8081 (E2E), 8080 (dev) |

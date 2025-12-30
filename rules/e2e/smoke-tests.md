---
paths: e2e/tests/**/*.ts
---

# Smoke Tests

Every page should have smoke tests that verify basic functionality. Smoke tests catch issues like:
- JavaScript errors on page load
- Missing dependencies or broken imports
- Server-side rendering errors
- Failed API calls during initial load

## Console Error Collection

Create a utility to collect console errors during page load:

### The Utility (`e2e/fixtures/console-errors.ts`)

```typescript
import { Page } from '@playwright/test'

/**
 * Patterns to ignore in console error collection.
 * Add project-specific patterns as needed.
 */
const IGNORED_ERROR_PATTERNS = [
  'favicon',           // Missing favicon
  'ResizeObserver',    // Benign resize observer errors
  'Failed to send',    // Logging failures
  '_debugbar',         // Laravel Debugbar
  'Xdebug',           // Xdebug messages
]

/**
 * Creates a collector that captures console errors during page interactions.
 *
 * Usage:
 * ```typescript
 * const errorCollector = createConsoleErrorCollector(page)
 * await page.goto('/some-page')
 * expect(errorCollector.getErrors()).toEqual([])
 * ```
 */
export function createConsoleErrorCollector(page: Page) {
  const errors: string[] = []

  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      const text = msg.text()
      const isIgnored = IGNORED_ERROR_PATTERNS.some((pattern) =>
        text.toLowerCase().includes(pattern.toLowerCase())
      )
      if (!isIgnored) {
        errors.push(text)
      }
    }
  })

  return {
    getErrors: () => errors,
    clear: () => { errors.length = 0 },
  }
}
```

## Smoke Test Pattern

### Basic Structure

```typescript
// e2e/tests/App/Dashboard/Index/smoke.spec.ts
import { test, expect } from '../../../../fixtures/base.fixture'
import { createConsoleErrorCollector } from '../../../../fixtures/console-errors'

/**
 * Smoke tests for the Dashboard Index page.
 * Verifies the page loads without errors.
 */
test.describe('Dashboard Index', () => {
  test('page loads without console errors', async ({ authenticatedPage }) => {
    const { page } = authenticatedPage
    const errorCollector = createConsoleErrorCollector(page)

    await page.goto('/app/dashboard')
    await page.waitForLoadState('networkidle')

    await expect(page).toHaveURL(/.*\/app\/dashboard/)
    expect(errorCollector.getErrors()).toEqual([])
  })

  test('displays page content', async ({ authenticatedPage }) => {
    const { page } = authenticatedPage

    await page.goto('/app/dashboard')
    await page.waitForLoadState('networkidle')

    // Verify key elements are visible
    await expect(page.getByRole('heading')).toBeVisible()
  })
})
```

### For Public Pages (No Auth)

```typescript
// e2e/tests/Auth/Login/smoke.spec.ts
import { test, expect } from '../../../fixtures/base.fixture'
import { createConsoleErrorCollector } from '../../../fixtures/console-errors'

test.describe('Login', () => {
  test('page loads without console errors', async ({ page }) => {
    // Note: using `page` not `authenticatedPage`
    const errorCollector = createConsoleErrorCollector(page)

    await page.goto('/login')
    await page.waitForLoadState('networkidle')

    await expect(page).toHaveURL(/.*\/login/)
    expect(errorCollector.getErrors()).toEqual([])
  })

  test('displays login form', async ({ page }) => {
    await page.goto('/login')

    await expect(page.getByRole('button', { name: /log in/i })).toBeVisible()
    await expect(page.getByLabel(/email/i)).toBeVisible()
    await expect(page.getByLabel(/password/i)).toBeVisible()
  })
})
```

## File Naming

Smoke tests should be in `smoke.spec.ts`:

```
e2e/tests/
├── App/
│   ├── Dashboard/
│   │   └── Index/
│   │       ├── smoke.spec.ts      ← Smoke tests
│   │       └── widgets.spec.ts    ← Feature tests
│   └── Settings/
│       └── Index/
│           └── smoke.spec.ts
└── Auth/
    └── Login/
        ├── smoke.spec.ts
        └── form.spec.ts
```

## What Smoke Tests Should Verify

### Minimum (every page)

1. **Page loads** - No server errors (500, 404)
2. **No console errors** - JavaScript executes without errors
3. **URL is correct** - Navigation worked

### Recommended (if applicable)

4. **Key heading visible** - Page rendered correctly
5. **Main content area exists** - Layout loaded
6. **No loading spinners stuck** - Data loaded

## Adding Project-Specific Ignored Patterns

If your project has known console messages that aren't errors, add them:

```typescript
const IGNORED_ERROR_PATTERNS = [
  // Framework noise
  'favicon',
  'ResizeObserver',

  // Project-specific
  'analytics',           // Analytics library warnings
  'third-party-widget',  // Known widget issues
]
```

## When Smoke Tests Fail

### Console error found

1. Check the error message
2. If it's noise, add to `IGNORED_ERROR_PATTERNS`
3. If it's real, fix the underlying issue

### Page doesn't load

1. Check server is running (`npm run e2e` handles this)
2. Check the route exists
3. Check authentication requirements

### Wrong URL

1. Verify the route path
2. Check for redirects (auth, permissions)
3. Verify fixture is providing correct auth level

## Best Practices

1. **Keep smoke tests simple** - They verify the page works, not specific features
2. **One smoke file per page** - `smoke.spec.ts` in each page's test directory
3. **Run smoke tests first** - They catch basic issues quickly
4. **Don't over-assert** - Smoke tests shouldn't be brittle
5. **Update ignored patterns** - When you add libraries that log warnings

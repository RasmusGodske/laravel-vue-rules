---
paths: e2e/tests/**/*.ts
---

# E2E Test Conventions

Guidelines for writing Playwright E2E tests.

**Related:** See `smoke-tests.md` for the smoke test pattern that every page should have.

## Test Directory Structure

**CRITICAL:** Test directories MUST mirror the structure of `resources/js/Pages/`, with each Vue page becoming a directory containing multiple test files.

```
resources/js/Pages/                    e2e/tests/
├── App/                               ├── App/
│   └── Commissions/                   │   └── Commissions/
│       ├── Index.vue          →       │       ├── Index/
│       │                              │       │   ├── filtering.spec.ts
│       │                              │       │   ├── sorting.spec.ts
│       │                              │       │   └── bulk-actions.spec.ts
│       ├── Create.vue         →       │       ├── Create/
│       │                              │       │   ├── validation.spec.ts
│       │                              │       │   └── submission.spec.ts
│       └── Edit.vue           →       │       └── Edit/
│                                      │           └── update-fields.spec.ts
└── Auth/                              └── Auth/
    └── Login.vue              →           └── Login/
                                               ├── form.spec.ts
                                               └── validation.spec.ts
```

### Directory Naming

- **Use PascalCase** to match Vue pages exactly
- `resources/js/Pages/App/Dashboard/Index.vue` → `e2e/tests/App/Dashboard/Index/`

### Test File Naming

- **Use kebab-case** for test file names
- Name files by **feature/functionality**, not by scenario
- Keep names concise but descriptive

```
Good:
├── smoke.spec.ts          # Page loads without errors (REQUIRED)
├── filtering.spec.ts      # All filtering-related tests
├── validation.spec.ts     # Form validation tests
├── crud.spec.ts           # Basic CRUD operations
├── permissions.spec.ts    # Access control tests
├── bulk-actions.spec.ts   # Bulk operation tests

Avoid:
├── filter-by-date-range-and-status.spec.ts  # Too specific
├── test1.spec.ts                             # Not descriptive
├── index.spec.ts                             # Doesn't describe what's tested
```

## Authentication Fixture

Use the `authenticatedPage` fixture for tests requiring a logged-in user:

```typescript
import { test, expect } from '../../../../fixtures/base.fixture'

test.describe('Dashboard', () => {
  test('shows user data', async ({ authenticatedPage }) => {
    const { page, testData } = authenticatedPage

    // page is already logged in as testData.user
    // Already on /app/dashboard after login

    await expect(page.getByText(testData.user.name)).toBeVisible()
  })
})
```

**What `authenticatedPage` provides:**
- `page` - Playwright page, already logged in
- `testData.user` - The created user (`id`, `email`, `name`, `password`)

**Important:** The fixture logs in and waits for the dashboard. Your test starts on the dashboard. Check your project's `e2e/fixtures/base.fixture.ts` for the exact post-login URL pattern.

## Available Fixtures

| Fixture | Use Case |
|---------|----------|
| `page` | Tests that don't need authentication (login page, public pages) |
| `authenticatedPage` | Tests that need a logged-in user (provides `{ page, testData }`) |
| `testData` | When you need test data but will handle auth yourself |
| `testDataHelper` | When you need manual control over data creation/cleanup |

## Test File Structure

```typescript
import { test, expect } from '../../../../fixtures/base.fixture'

/**
 * Brief description of what this test file covers.
 */
test.describe('Feature Name', () => {
  test.describe('sub-feature or scenario group', () => {
    test('describes expected behavior', async ({ authenticatedPage }) => {
      const { page, testData } = authenticatedPage

      // Arrange - set up test state
      // Act - perform actions
      // Assert - verify results
    })
  })
})
```

## Grouping with test.describe()

Use nested `test.describe()` blocks to organize related scenarios within a file:

```typescript
// e2e/tests/App/Commissions/Index/filtering.spec.ts
test.describe('Filtering', () => {
  test.describe('by date range', () => {
    test('filters results when date range selected', async ({ authenticatedPage }) => {
      const { page } = authenticatedPage
      // ...
    })

    test('clears results when reset clicked', async ({ authenticatedPage }) => {
      const { page } = authenticatedPage
      // ...
    })
  })

  test.describe('by status', () => {
    test('shows only active items', async ({ authenticatedPage }) => {
      const { page } = authenticatedPage
      // ...
    })
  })
})
```

## Test Naming

Use descriptive names that explain the expected behavior:

```typescript
// Good - describes behavior
test('user can submit form with valid data', ...)
test('shows error when email is invalid', ...)
test('redirects to dashboard after login', ...)
test('disables submit button while processing', ...)

// Bad - vague or implementation-focused
test('test form', ...)
test('click button', ...)
test('test1', ...)
```

## Assertions

Use Playwright's built-in assertions (auto-waiting):

```typescript
// Good - auto-waits for condition
await expect(page.getByRole('heading')).toBeVisible()
await expect(page.getByText('Success')).toBeVisible()
await expect(page).toHaveURL('/dashboard')

// Bad - manual waits
await page.waitForTimeout(1000)  // Never use fixed timeouts
if (await page.locator('.success').isVisible()) { ... }  // Race condition
```

## Test Isolation

Each test gets its own:
- Fresh test data (customer, user)
- Logged-in session
- Automatic cleanup after test

```typescript
// Each test is completely isolated
test('test 1', async ({ authenticatedPage }) => {
  // Has its own user, customer, session
})

test('test 2', async ({ authenticatedPage }) => {
  // Has a DIFFERENT user, customer, session
  // Cannot see data from test 1
})
```

## Quick Reference

| Aspect | Convention |
|--------|------------|
| Directory case | PascalCase (matches Vue pages) |
| File names | kebab-case |
| File content | Feature-focused, multiple scenarios |
| Grouping | Use `test.describe()` for sub-features |
| Test names | Descriptive behavior statements |
| Auth tests | Use `authenticatedPage` fixture |
| No-auth tests | Use `page` fixture directly |

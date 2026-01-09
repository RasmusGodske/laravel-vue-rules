---
paths: e2e/tests/**/*.ts
---

# E2E Test Conventions

Guidelines for writing Playwright E2E tests.

**Related:** See `smoke-tests.md` for the smoke test pattern that every page should have.

## Test Directory Structure

**CRITICAL:** Route tests MUST be placed in `e2e/tests/routes/` and mirror the URL route structure, NOT the Vue file structure.

### Why Routes, Not Files?

1. **One Form.vue → Multiple routes**: A single `Form.vue` often handles both `/create` and `/{id}/edit`
2. **User perspective**: Users interact with routes, not files
3. **Different test scenarios**: Create and Edit have different data requirements
4. **Claude can validate**: Routes can be validated against Laravel's route list

### Route-Based Structure

```
URL Routes                             e2e/tests/routes/
                                       ├── app/
GET /app/users                   →     │   └── users/
                                       │       ├── index/
                                       │       │   ├── smoke.spec.ts
                                       │       │   ├── filtering.spec.ts
                                       │       │   └── bulk-actions.spec.ts
GET /app/users/create            →     │       ├── create/
                                       │       │   ├── smoke.spec.ts
                                       │       │   └── validation.spec.ts
GET /app/users/{id}/edit         →     │       └── edit/
                                       │           ├── smoke.spec.ts
                                       │           └── update-fields.spec.ts
                                       └── auth/
GET /login                       →         ├── login/
                                           │   ├── smoke.spec.ts
                                           │   └── form.spec.ts
GET /register                    →         └── register/
                                               └── smoke.spec.ts
```

### Directory Naming

- **Match route segments exactly** - use the same casing as the Laravel route
- `GET /app/users` → `e2e/tests/routes/app/users/index/`
- `GET /app/user_settings/create` → `e2e/tests/routes/app/user_settings/create/`
- Run `php artisan route:list --method=GET` to see exact route paths

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

## Verifying Form Submissions

**Never hardcode URL patterns in tests.** Use the destination page object instead.

```typescript
// ❌ BAD - Hardcoded URL pattern
await createPage.form.submit()
await page.waitForURL(/.*\/users/, { timeout: 15000 })

// ✅ GOOD - Use destination page object
const indexPage = new UsersIndexPage(page)
await createPage.form.submit()
await indexPage.expectNavigatedTo()
```

**Better: Verify the data actually appears in the list:**

```typescript
// ✅ BEST - Verify redirect AND data persistence
const indexPage = new UsersIndexPage(page)
const userName = 'New Test User'

await createPage.form.fill({ name: userName, email: 'test@example.com' })
await createPage.form.submit()

await indexPage.expectNavigatedTo()
await indexPage.expectRowVisible(userName)  // User appears in list!
```

**Why?**
- URL patterns defined once in page objects, not duplicated in tests
- Tests verify actual user-visible outcomes, not just redirects
- If URLs change, update one page object instead of many tests

See `page-objects.md` → "Index Page Pattern" for the page object structure.

## Quick Reference

| Aspect | Convention |
|--------|------------|
| Route tests location | `e2e/tests/routes/` |
| Directory names | Match Laravel route segments exactly |
| File names | kebab-case |
| File content | Feature-focused, multiple scenarios |
| Grouping | Use `test.describe()` for sub-features |
| Test names | Descriptive behavior statements |
| Auth tests | Use `authenticatedPage` fixture |
| No-auth tests | Use `page` fixture directly |
| Form submission | Use destination page object, not hardcoded URLs |

---
paths: e2e/pages/**/*.ts
---

# Page Object Model Conventions

Page Objects encapsulate page-specific selectors and actions, making tests more readable and maintainable.

## File Structure - Mirror Vue Pages

**CRITICAL:** Page Objects MUST mirror the structure of `resources/js/Pages/`.

```
resources/js/Pages/Auth/Login.vue           →  e2e/pages/Auth/Login.page.ts
resources/js/Pages/App/Dashboard/Index.vue  →  e2e/pages/App/Dashboard/Index.page.ts
resources/js/Pages/App/Settings/Form.vue    →  e2e/pages/App/Settings/Form.page.ts
```

### Naming Convention

- File name: `{PageName}.page.ts` (e.g., `Login.page.ts`, `Index.page.ts`)
- Class name: `{PageName}Page` (e.g., `LoginPage`, `DashboardIndexPage`)

## Page Object Structure

```typescript
import { Page, Locator, expect } from '@playwright/test'

/**
 * Page Object for {PageName}.
 *
 * Mirrors: resources/js/Pages/{Path}/{PageName}.vue
 */
export class {PageName}Page {
  readonly page: Page

  // Locators - define all element selectors
  readonly submitButton: Locator
  readonly titleHeading: Locator

  constructor(page: Page) {
    this.page = page
    this.submitButton = page.getByRole('button', { name: /submit/i })
    this.titleHeading = page.getByRole('heading', { level: 1 })
  }

  // Navigation
  async goto() {
    await this.page.goto('/path/to/page')
  }

  // Actions - what users can DO on this page
  async fillForm(data: FormData) {
    // ...
  }

  async submit() {
    await this.submitButton.click()
  }

  // Assertions - what we can VERIFY on this page
  async expectLoaded() {
    await expect(this.titleHeading).toBeVisible()
  }

  async expectError(message: string) {
    await expect(this.page.locator('.error')).toContainText(message)
  }
}
```

## Locator Best Practices

**Prefer (in order):**
1. `getByRole()` - Most resilient, matches accessibility tree
2. `getByTestId()` - Explicit test hooks
3. `getByText()` / `getByLabel()` - User-visible text
4. `locator()` with CSS - Last resort

```typescript
// Best - role-based
this.loginButton = page.getByRole('button', { name: /log in/i })

// Good - test ID
this.emailInput = page.getByTestId('email-input')

// Okay - label text
this.emailInput = page.getByLabel('Email')

// Avoid - brittle CSS selectors
this.emailInput = page.locator('.form-group:nth-child(2) input')
```

## Barrel Export

Always export new page objects from `e2e/pages/index.ts`:

```typescript
// e2e/pages/index.ts

// Auth pages
export { LoginPage } from './Auth/Login.page'

// App pages
export { DashboardIndexPage } from './App/Dashboard/Index.page'
export { SettingsFormPage } from './App/Settings/Form.page'
```

## When to Create a Page Object

Create a page object when:
- The page is tested in multiple test files
- The page has complex interactions
- Selectors might change and you want single-point maintenance

Skip page objects for:
- One-off simple tests
- Pages with trivial interactions

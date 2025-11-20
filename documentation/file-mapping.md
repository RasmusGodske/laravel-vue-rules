# File-to-Doc Mapping

This file defines the conventions for mapping code files to documentation files. These conventions help both humans and the research agent know exactly where documentation should live.

---

## Core Principle

**Domains map to business capabilities** (not 1:1 to code)
**Layers map to code structure** (1:1 mapping)

---

## Domain Documentation Mapping

Domains are **not** direct code mappings - they represent business capabilities.

### Mapping Strategy

1. **Identify the business capability** (what user need does it serve?)
2. **Group related features** under that domain
3. **Create domain directory** with features subdirectory

### Examples

| Business Capability | Domain Path | Features |
|---------------------|-------------|----------|
| User authentication | `domains/authentication/` | login.md, registration.md, password-recovery.md, jwt-validation.md |
| E-commerce | `domains/ecommerce/` | product-catalog.md, shopping-cart.md, checkout.md, order-tracking.md |
| User profiles | `domains/user-management/` | profiles.md, permissions.md, settings.md |
| Notifications | `domains/notifications/` | email-notifications.md, push-notifications.md, in-app-alerts.md |

### Domain Feature Mapping

Feature documentation lives in: `docs/domains/{domain}/features/{feature}.md`

**Examples:**
- "User Login" → `docs/domains/authentication/features/login.md`
- "Product Catalog" → `docs/domains/ecommerce/features/product-catalog.md`
- "Two-Factor Auth" → `docs/domains/authentication/features/two-factor-auth.md`
- "Order Checkout" → `docs/domains/ecommerce/features/checkout.md`

---

## Layer Documentation Mapping

Layers map **directly to code structure**. Use these conventions for predictable mapping.

### Backend Mappings

| Code Path | Documentation Path | Example |
|-----------|-------------------|---------|
| `app/Services/{Name}/` | `docs/layers/backend/services/{name}-service.md` | `app/Services/Auth/` → `auth-service.md` |
| `app/Models/{Name}.php` | `docs/layers/backend/models/{name}-model.md` | `app/Models/User.php` → `user-model.md` |
| `app/Http/Controllers/{Name}Controller.php` | `docs/layers/backend/controllers/{name}-controller.md` | `app/Http/Controllers/AuthController.php` → `auth-controller.md` |
| `app/Http/Middleware/{Name}.php` | `docs/layers/backend/middleware/{name}.md` | `app/Http/Middleware/Authenticate.php` → `authenticate.md` |
| `app/Jobs/{Name}.php` | `docs/layers/backend/jobs/{name}.md` | `app/Jobs/ProcessOrder.php` → `process-order.md` |
| `app/Events/{Name}.php` | `docs/layers/backend/events/{name}.md` | `app/Events/OrderCreated.php` → `order-created.md` |

**Note:** Service directories map to a single service doc (e.g., `app/Services/Auth/JwtValidator.php` and `app/Services/Auth/SessionManager.php` might both be documented in `auth-service.md`)

### Frontend Mappings

| Code Path | Documentation Path | Example |
|-----------|-------------------|---------|
| `resources/js/components/{Name}.vue` | `docs/layers/frontend/components/{name}.md` | `PaymentForm.vue` → `payment-form.md` |
| `resources/js/composables/{name}.ts` | `docs/layers/frontend/composables/{name}.md` | `useAuth.ts` → `use-auth.md` |
| `resources/js/views/{Name}.vue` | `docs/layers/frontend/views/{name}.md` | `Dashboard.vue` → `dashboard.md` |
| `resources/js/stores/{name}.ts` | `docs/layers/frontend/stores/{name}.md` | `userStore.ts` → `user-store.md` |
| `resources/js/utils/{name}.ts` | `docs/layers/frontend/utils/{name}.md` | `formatDate.ts` → `format-date.md` |

### Database Mappings

| Code Path | Documentation Path | Example |
|-----------|-------------------|---------|
| Table schema | `docs/layers/database/schema/{table-name}.md` | `users` table → `users.md` |
| Migrations | `docs/layers/database/migrations.md` | All migrations → single file |

---

## File Naming Rules

### Converting Code Names to Doc Names

1. **Remove file extension:** `User.php` → `User`
2. **Remove suffixes:** `AuthController` → `Auth`, `UserModel` → `User`
3. **Convert to lowercase:** `Auth` → `auth`
4. **Add hyphens:** `TwoFactorAuth` → `two-factor-auth`
5. **Add suffix if needed:** `auth` → `auth-service.md`

### Examples

| Code File | Doc File |
|-----------|----------|
| `AuthService.php` | `auth-service.md` |
| `UserController.php` | `user-controller.md` |
| `PaymentForm.vue` | `payment-form.md` |
| `useAuth.ts` | `use-auth.md` |
| `OrderCreated.php` | `order-created.md` |

---

## Search Patterns for Research Agent

When researching a topic, use these patterns to find relevant documentation:

### Topic: "Authentication JWT Validation"

**Domain Search:**
1. `docs/domains/authentication/README.md`
2. `docs/domains/authentication/features/*.md` (glob for jwt*, token*, valid*)

**Layer Search:**
3. `docs/layers/backend/services/*auth*.md`
4. `docs/layers/backend/middleware/*auth*.md`

**Code Search:**
5. `app/Services/Auth/`
6. `app/Http/Middleware/Authenticate.php`

### Topic: "Payment Processing Flow"

**Domain Search:**
1. `docs/domains/ecommerce/README.md` or `docs/domains/payments/README.md`
2. `docs/domains/*/features/*payment*.md`

**Layer Search:**
3. `docs/layers/backend/services/*payment*.md`
4. `docs/layers/frontend/components/*payment*.md`

**Code Search:**
5. `app/Services/Payment/`
6. `resources/js/components/*Payment*.vue`

### Topic: "User Profile Management"

**Domain Search:**
1. `docs/domains/user-management/README.md`
2. `docs/domains/user-management/features/profiles.md`

**Layer Search:**
3. `docs/layers/backend/models/user-model.md`
4. `docs/layers/frontend/components/*profile*.md`

**Code Search:**
5. `app/Models/User.php`
6. `resources/js/components/*Profile*.vue`

---

## Mapping Edge Cases

### Multiple Classes in One Service Directory

**Code:**
```
app/Services/Auth/
├── JwtValidator.php
├── SessionManager.php
└── PasswordHasher.php
```

**Documentation:**
- Single doc: `docs/layers/backend/services/auth-service.md`
- Document all classes in one file under different sections
- Link to code files specifically: `app/Services/Auth/JwtValidator.php:23`

### Shared Components Across Domains

**Example:** A "Modal" component used in multiple domains

**Solution:**
- Document in layers: `docs/layers/frontend/components/modal.md`
- Reference from domain docs: "See [Modal component](../../layers/frontend/components/modal.md)"
- Domain docs focus on usage, layer docs focus on implementation

### Feature Spans Multiple Layers

**Example:** "User Login" involves backend, frontend, and database

**Solution:**
- **Domain doc** (`domains/authentication/features/login.md`): High-level flow, references to all layers
- **Layer docs:** Individual component details
- Link between them generously

---

## Quick Reference Table

| Question | Answer |
|----------|--------|
| Where does business logic documentation go? | `docs/domains/{domain}/features/{feature}.md` |
| Where do I document a service class? | `docs/layers/backend/services/{service-name}-service.md` |
| Where do I document a Vue component? | `docs/layers/frontend/components/{component-name}.md` |
| How do I name documentation files? | lowercase-with-hyphens.md |
| Can one feature have multiple docs? | Yes, break into sub-features if needed |
| Can multiple code files map to one doc? | Yes, for service directories and related classes |

---

## Related Files

- **structure.md** - Overall documentation organization
- **templates.md** - What to put in each type of documentation
- **index-maintenance.md** - How to make docs discoverable

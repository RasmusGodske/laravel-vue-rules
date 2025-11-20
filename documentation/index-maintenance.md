# INDEX.md Maintenance

This file defines how to maintain `docs/INDEX.md` - the master index for documentation discoverability.

---

## Why INDEX.md Matters

The INDEX.md file is the **entry point** for finding documentation. It provides:
- Quick lookup for specific topics
- Overview of what's documented
- Links to all major documentation

**Without a maintained INDEX.md, documentation becomes hard to discover.**

---

## INDEX.md Structure

```markdown
# Documentation Index

Last updated: YYYY-MM-DD HH:MM:SS

This index provides quick links to all documentation.

## Domains

- [Domain Name](domains/domain-name/README.md) - Brief description
- [Another Domain](domains/another/README.md) - Brief description

## Backend

### Services
- [ServiceName](layers/backend/services/service-name.md) - `app/Services/Name/` - Brief description

### Models
- [ModelName](layers/backend/models/model-name.md) - `app/Models/Name.php` - Brief description

### Middleware
- [MiddlewareName](layers/backend/middleware/middleware-name.md) - `app/Http/Middleware/Name.php` - Brief description

## Frontend

### Components
- [ComponentName](layers/frontend/components/component-name.md) - `resources/js/components/Name.vue` - Brief description

### Composables
- [ComposableName](layers/frontend/composables/composable-name.md) - `resources/js/composables/name.ts` - Brief description

## Database

- [TableName](layers/database/schema/table-name.md) - Table description

---
*This index is automatically maintained by the documentation system.*
*See `.claude/rules/documentation/README.md` for conventions.*
```

---

## When to Update INDEX.md

**Update INDEX.md whenever you:**
- ✅ Create a new domain
- ✅ Create a new feature (add to domain section)
- ✅ Create new layer documentation (service, component, etc.)
- ✅ Delete or rename documentation
- ✅ Reorganize documentation structure

**Critical rule:** INDEX.md should be updated in the **same commit** as creating/modifying documentation.

---

## Entry Format

### Domain Entries

```markdown
- [Domain Name](domains/domain-name/README.md) - Brief description
```

**Components:**
1. **Link text:** Capitalized domain name
2. **Link path:** Relative path from docs/ root
3. **Description:** 1-10 words describing what this domain covers

**Examples:**
```markdown
- [Authentication](domains/authentication/README.md) - User auth, JWT, sessions, password recovery
- [E-commerce](domains/ecommerce/README.md) - Products, orders, payments, inventory
- [Notifications](domains/notifications/README.md) - Email, push, and in-app alerts
```

### Layer Entries

```markdown
- [ComponentName](layers/{layer}/{type}/component-name.md) - `code/path/` - Brief description
```

**Components:**
1. **Link text:** Component name (CamelCase or readable form)
2. **Link path:** Relative path from docs/ root
3. **Code path:** Actual path in codebase
4. **Description:** What it does

**Examples:**
```markdown
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - JWT validation and session management
- [PaymentForm](layers/frontend/components/payment-form.md) - `resources/js/components/PaymentForm.vue` - Stripe payment processing UI
- [User](layers/backend/models/user-model.md) - `app/Models/User.php` - User model with authentication traits
```

---

## Organization Rules

### Alphabetical Order

**Within each section, maintain alphabetical order by link text:**

**Good:**
```markdown
## Domains
- [Authentication](domains/authentication/README.md) - User auth
- [E-commerce](domains/ecommerce/README.md) - Online store
- [Notifications](domains/notifications/README.md) - Alerts
```

**Bad:**
```markdown
## Domains
- [E-commerce](domains/ecommerce/README.md) - Online store
- [Notifications](domains/notifications/README.md) - Alerts
- [Authentication](domains/authentication/README.md) - User auth
```

### Grouping

**Backend layer entries should be grouped by type:**
- Services
- Models
- Controllers
- Middleware
- Jobs
- Events

**Frontend layer entries should be grouped by type:**
- Components
- Composables
- Views
- Stores

### Example of Good Organization

```markdown
## Backend

### Services
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - Authentication
- [PaymentService](layers/backend/services/payment-service.md) - `app/Services/Payment/` - Payments

### Models
- [Order](layers/backend/models/order-model.md) - `app/Models/Order.php` - Order model
- [User](layers/backend/models/user-model.md) - `app/Models/User.php` - User model

### Middleware
- [Authenticate](layers/backend/middleware/authenticate.md) - `app/Http/Middleware/Authenticate.php` - JWT validation
```

---

## Timestamp Maintenance

**Always update the "Last updated" timestamp when modifying INDEX.md:**

```markdown
Last updated: 2025-11-20 14:30:22
```

**Format:** `YYYY-MM-DD HH:MM:SS`

---

## Description Guidelines

### Length

Keep descriptions concise:
- **Domains:** 5-15 words
- **Components:** 3-10 words

### Content

**For domains:**
- List key features or capabilities
- Use commas to separate multiple concerns

**For components:**
- Describe the primary responsibility
- Mention key technology if relevant (JWT, Stripe, Redis, etc.)

### Examples

**Good domain descriptions:**
```markdown
- User authentication, authorization, JWT tokens, password recovery
- Product catalog, shopping cart, checkout, order processing
- Email notifications, push alerts, in-app messages
```

**Good component descriptions:**
```markdown
- JWT token validation and session management
- Stripe payment processing and webhook handling
- User authentication and authorization middleware
- Product listing with filtering and pagination
```

**Bad descriptions:**
```markdown
- Does stuff with users (too vague)
- The main authentication service (redundant)
- Service for authentication (obvious from the name)
```

---

## Adding New Entries

### Step-by-Step Process

1. **Read current INDEX.md** to understand existing structure
2. **Determine the section** (Domains, Backend, Frontend, Database)
3. **Determine the subsection** (Services, Components, etc.)
4. **Find alphabetical position** within that subsection
5. **Add entry** using correct format
6. **Update timestamp**
7. **Verify links** work correctly

### Example

Adding documentation for `PaymentService`:

**Before:**
```markdown
## Backend

### Services
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - Authentication
- [UserService](layers/backend/services/user-service.md) - `app/Services/User/` - User management
```

**After:**
```markdown
## Backend

### Services
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - Authentication
- [PaymentService](layers/backend/services/payment-service.md) - `app/Services/Payment/` - Stripe payments
- [UserService](layers/backend/services/user-service.md) - `app/Services/User/` - User management
```

---

## Removing Entries

When deleting documentation:

1. **Remove the entry** from INDEX.md
2. **Update timestamp**
3. **Check for broken links** elsewhere that pointed to this doc
4. **Update related documentation** that referenced this

---

## Renaming Entries

When renaming documentation:

1. **Update the link path** in INDEX.md
2. **Update the link text** if needed
3. **Update timestamp**
4. **Check other docs** for links to the old path
5. **Update related links** throughout documentation

---

## Validation Checklist

Before committing INDEX.md changes:

- [ ] All links are valid (point to existing files)
- [ ] All links use relative paths from docs/ root
- [ ] Entries are alphabetically sorted within sections
- [ ] Descriptions are concise and informative
- [ ] Code paths are included for layer documentation
- [ ] Timestamp is updated
- [ ] No duplicate entries
- [ ] Consistent formatting across all entries

---

## Automation Considerations

### Current State

INDEX.md is maintained manually by developers and the documentation generation system.

### Future Enhancement

INDEX.md could be automatically generated from:
- Domain README.md files
- Layer documentation files
- Frontmatter metadata

**For now: maintain manually** to ensure quality and proper organization.

---

## Common Mistakes

### Mistake 1: Absolute Paths

**Wrong:**
```markdown
- [AuthService](/docs/layers/backend/services/auth-service.md)
```

**Right:**
```markdown
- [AuthService](layers/backend/services/auth-service.md)
```

### Mistake 2: Missing Code Paths

**Wrong:**
```markdown
- [AuthService](layers/backend/services/auth-service.md) - Authentication service
```

**Right:**
```markdown
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - Authentication service
```

### Mistake 3: Inconsistent Formatting

**Wrong:**
```markdown
- [AuthService](layers/backend/services/auth-service.md) - app/Services/Auth/ - Authentication
- [UserService](layers/backend/services/user-service.md) - `app/Services/User/` (User management)
```

**Right:**
```markdown
- [AuthService](layers/backend/services/auth-service.md) - `app/Services/Auth/` - Authentication
- [UserService](layers/backend/services/user-service.md) - `app/Services/User/` - User management
```

### Mistake 4: Not Updating Timestamp

Always update the timestamp when modifying INDEX.md.

---

## Related Files

- **structure.md** - Overall documentation organization
- **file-mapping.md** - Where documentation files should live
- **common-mistakes.md** - Additional validation checks

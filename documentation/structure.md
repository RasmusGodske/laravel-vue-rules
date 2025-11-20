# Documentation Structure

This file defines how documentation is organized in the `docs/` directory.

---

## Directory Overview

```
docs/
├── README.md                    # Documentation system overview
├── INDEX.md                     # Master index for quick lookup
├── domains/                     # Business domains and features
├── layers/                      # Technical implementation layers
└── _research/                   # Research artifacts (internal)
```

---

## Domains Directory

**Purpose:** Document business capabilities and user-facing features.

**Structure:**
```
docs/domains/{domain-name}/
├── README.md              # Domain overview
├── features/              # Features within this domain
│   ├── feature-1.md
│   └── feature-2.md
├── architecture.md        # Optional: Domain-specific architecture
└── api.md                # Optional: Domain API overview
```

**Examples:**
- `docs/domains/authentication/` - Login, registration, password recovery, JWT
- `docs/domains/ecommerce/` - Products, orders, payments, inventory
- `docs/domains/user-management/` - Profiles, permissions, settings
- `docs/domains/notifications/` - Email, push, in-app notifications

**When to create a domain:**
- You're adding a major business capability
- Multiple related features will live under it
- It represents a bounded context in your system
- Group of features that belong together conceptually

---

## Layers Directory

**Purpose:** Document technical implementation organized by architecture layer.

**Structure:**

### Backend Layer
```
docs/layers/backend/
├── README.md
├── architecture.md
├── services/
│   └── {service-name}-service.md
├── models/
│   └── {model-name}-model.md
├── middleware/
│   └── {middleware-name}.md
└── controllers/
    └── {controller-name}-controller.md
```

### Frontend Layer
```
docs/layers/frontend/
├── README.md
├── architecture.md
├── components/
│   └── {component-name}.md
├── composables/
│   └── {composable-name}.md
└── views/
    └── {view-name}.md
```

### Database Layer
```
docs/layers/database/
├── README.md
└── schema/
    └── {table-name}.md
```

**Examples:**
- `docs/layers/backend/services/auth-service.md` - AuthService implementation
- `docs/layers/frontend/components/payment-form.md` - PaymentForm component
- `docs/layers/database/schema/users.md` - Users table schema

---

## Research Directory

**Purpose:** Internal artifacts from the research-driven documentation workflow.

**Structure:**
```
docs/_research/
├── lacking/                   # Documentation gap reports
│   ├── pending/              # Awaiting processing
│   │   └── {timestamp}_{slug}/
│   │       └── report.md
│   ├── in-progress/          # Currently being processed
│   └── processed/            # Completed
│       └── {timestamp}_{slug}/
│           ├── report.md
│           ├── plan.md
│           └── resolution.md
└── summaries/                # Quick summaries for Claude
    └── {timestamp}_{slug}/
        └── summary.md
```

**Report Directory Naming:**
- Format: `{timestamp}_{topic-slug}/`
- Timestamp: `YYYY-MM-DD_HHMMSS` (e.g., `2025-11-20_143022`)
- Slug: lowercase-with-hyphens (e.g., `auth-jwt-validation`)
- Example: `2025-11-20_143022_auth-jwt-validation/`

**Note:** This directory is typically gitignored or kept for historical tracking.

---

## When to Create Documentation

### Domain-Level

Create a new domain when:
- Adding a major business capability (authentication, e-commerce, analytics)
- Multiple related features will live under it
- It represents a distinct area of functionality
- It maps to a team's ownership or bounded context

**Examples of good domains:**
- ✅ `authentication` - Clear business capability
- ✅ `order-processing` - Specific business process
- ✅ `reporting` - Distinct functional area
- ❌ `utils` - Too generic
- ❌ `helpers` - Not a business capability

### Feature-Level

Document a feature when:
- It involves multiple components/services working together
- It has complex business logic or workflows
- It's user-facing and non-trivial
- It's frequently modified or questioned by the team
- It requires understanding of business rules

**Examples of features to document:**
- ✅ Login with two-factor authentication
- ✅ Order checkout and payment
- ✅ Password recovery flow
- ❌ Simple CRUD for a single entity (unless complex business rules)

### Layer-Level

Document a component/class when:
- It's non-trivial (> 100 lines or complex logic)
- It provides core functionality used by other parts of the system
- It has a public API that other developers need to understand
- It would benefit from architectural explanation
- It has configuration or setup requirements

**Examples of components to document:**
- ✅ AuthService with JWT validation logic
- ✅ PaymentForm with Stripe integration
- ✅ CacheManager with multiple strategies
- ❌ Simple getters/setters
- ❌ Thin wrapper around library with no custom logic

---

## File Naming Conventions

**Always use lowercase-with-hyphens:**
- ✅ `auth-service.md`
- ✅ `password-recovery.md`
- ✅ `payment-form.md`
- ❌ `AuthService.md`
- ❌ `passwordRecovery.md`
- ❌ `payment_form.md`

**Domain and feature names:**
- Use descriptive, business-focused names
- Avoid technical jargon in domain names
- Keep feature names focused and specific

---

## Documentation Scale

### Small Projects (< 50 files)
- Start with 2-3 domains
- Simple feature documentation
- Layer docs for core services/components only
- Keep it minimal, expand as needed

### Medium Projects (50-500 files)
- 5-10 domains
- Comprehensive feature documentation
- Layer docs for most non-trivial components
- Clear domain boundaries

### Large Projects (500+ files)
- 10+ domains
- Detailed feature and layer documentation
- Architecture docs per domain
- INDEX.md becomes critical

**Start simple, expand as needed.** Don't over-document early.

---

## Related Files

- **file-mapping.md** - Where specific code files map to documentation
- **templates.md** - Templates for domain, feature, and layer docs
- **index-maintenance.md** - How to keep INDEX.md updated

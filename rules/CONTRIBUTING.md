---
paths:
  - ".claude/rules/**/*.md"
---

# Contributing to Techstack Rules

## 🚨 STOP: Before Writing ANY Techstack Rule

**Ask yourself: Are my examples project-agnostic?**

- ❌ `Commission`, `CommissionPlan`, `Trigger`, `DataSource`, `CustomerUser` → **NO!**
- ✅ `Order`, `Product`, `User`, `Cart`, `Post`, `Comment` → **YES!**

If you use project-specific names, the rule is **WRONG**. Fix it before proceeding.

---

## 🚨 CRITICAL: These Rules Are Shared Across Projects

The rules in `.claude/rules/techstack/` are **project-agnostic** and stored in a separate repository:

**Source Repository:** https://github.com/RasmusGodske/laravel-vue-rules

When you modify any file in `.claude/rules/techstack/`:
1. The change only affects THIS project locally
2. You MUST also update the source repository for changes to propagate to other projects
3. Ask the user if they want to push changes to the source repo

## Project-Specific vs Techstack Rules

| Location | Purpose | Examples Should Use |
|----------|---------|---------------------|
| `.claude/rules/techstack/` | Generic Laravel/Vue patterns | Generic names: `Order`, `User`, `Product`, `Report` |
| `.claude/rules/project/` | This project's specific patterns | Project names: `Commission`, `CommissionPlan`, `DataSource` |

## Writing Good Rules

### ❌ DON'T: Be Overly Detailed

Long, exhaustive rules are:
- Hard to read
- Consume too many tokens
- Often ignored because they're overwhelming

```markdown
❌ BAD - Way too long
# Database Query Patterns

When writing database queries in Laravel, you should always consider
the following factors: performance implications, N+1 query problems,
memory usage, database connection pooling, query caching strategies,
index utilization, and the specific database engine being used...

[500 more lines of exhaustive detail]
```

### ✅ DO: Be Short and Concise

Rules should be:
- Scannable in seconds
- Focused on ONE concept
- Actionable, not theoretical

```markdown
✅ GOOD - Short and actionable
# Eager Loading

Always eager load relationships to prevent N+1 queries:

```php
// ✅ Good
User::with(['posts', 'comments'])->get();

// ❌ Bad - causes N+1
User::all()->each(fn($u) => $u->posts);
```
```

### ❌ DON'T: Use Project-Specific Examples in Techstack Rules

Techstack rules must work for ANY Laravel/Vue project:

```markdown
❌ BAD - Project-specific examples
# Service Patterns

class CommissionCalculationService
{
    public function calculateForPlan(CommissionPlan $plan): Money
    {
        return $plan->triggers->sum(...);
    }
}
```

### ✅ DO: Use Generic Domain Examples

Use universally understood domain concepts:

```markdown
✅ GOOD - Generic examples
# Service Patterns

class OrderCalculationService
{
    public function calculateTotal(Order $order): Money
    {
        return $order->items->sum(...);
    }
}
```

**Good generic domains for examples:**
- E-commerce: `Order`, `Product`, `Cart`, `Customer`
- Users: `User`, `Team`, `Role`, `Permission`
- Content: `Post`, `Comment`, `Category`, `Tag`
- Generic: `Item`, `Entry`, `Record`, `Entity`

**Never use in techstack rules:**
- `Commission`, `CommissionPlan`, `Trigger`
- `DataSource`, `DataSourceColumn`
- `CustomerUser`, `ObjectDefinition`
- Any class specific to this codebase

## Rule Structure Guidelines

### 1. Start with the Core Principle

```markdown
# Rule Title

> **One sentence summary of the rule.**

[Rest of the rule...]
```

### 2. Show Don't Tell

Prefer code examples over prose:

```markdown
❌ BAD - Too much prose
When creating a service class, you should ensure that
dependencies are injected through the constructor rather
than being instantiated inside the class methods...

✅ GOOD - Code speaks
```php
// ✅ Inject dependencies
class OrderService
{
    public function __construct(
        private PaymentGateway $gateway
    ) {}
}

// ❌ Don't instantiate inside
class OrderService
{
    public function process()
    {
        $gateway = new PaymentGateway(); // Bad
    }
}
```
```

### 3. Keep Examples Minimal

Show the minimum code needed to illustrate the point:

```markdown
❌ BAD - Too much code
class UserRegistrationService
{
    private UserRepository $userRepository;
    private EmailService $emailService;
    private EventDispatcher $eventDispatcher;
    private Logger $logger;

    public function __construct(
        UserRepository $userRepository,
        EmailService $emailService,
        EventDispatcher $eventDispatcher,
        Logger $logger
    ) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
        // ... 50 more lines
    }
}

✅ GOOD - Minimal example
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService
    ) {}
}
```

### 4. Use Consistent Formatting

- Use `✅ GOOD` and `❌ BAD` markers
- Put the good example first when showing alternatives
- Use code fences with language hints (```php, ```typescript)

## Frontmatter Requirements

Every rule file MUST have frontmatter with paths:

```yaml
---
paths:
  - "app/**/*.php"           # PHP files
  - "resources/js/**/*.vue"  # Vue files
---
```

Rules without `paths` load for ALL files (use sparingly).

## File Organization

```
.claude/rules/techstack/
├── backend/           # PHP/Laravel patterns
│   ├── controller-conventions.md
│   ├── service-patterns.md
│   └── testing-conventions.md
├── frontend/          # Vue/TypeScript patterns
│   ├── vue-conventions.md
│   └── tailwind-conventions.md
├── dataclasses/       # Data class patterns
│   └── laravel-data.md
├── principles/        # Cross-cutting principles
│   └── code-organization.md
└── CONTRIBUTING.md    # This file
```

## Checklist Before Committing Techstack Rules

- [ ] **VERIFY EXAMPLES ARE GENERIC** - Re-read every example and confirm NO project-specific names
- [ ] Examples use generic domains (Order, User, Product), not project-specific
- [ ] Rule is concise (aim for < 100 lines, exceptions for comprehensive guides)
- [ ] Frontmatter has appropriate `paths`
- [ ] Code examples are minimal and focused
- [ ] Remember to update source repo: https://github.com/RasmusGodske/laravel-vue-rules

### Common Mistake: Project-Specific Examples

Even after reading this guide, it's easy to slip into using project-specific examples. **Always double-check:**

```
❌ WRONG (project-specific):
- agent_id, sale_amount, sale_date
- commission_plan_id, trigger_id
- customer_user_id, data_source_id

✅ CORRECT (generic):
- user_id, product_id, order_id
- quantity, price, total
- customer_id, category_id
```

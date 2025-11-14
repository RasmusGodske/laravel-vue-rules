# Backend Development Rules

**⚠️ MANDATORY:** Read this file before writing any backend code.

---

## Required Convention Files

Read these files in order based on what you're working on. Each file states when it should be read.

### 1. `php-conventions.md` - READ FIRST

**When to read:** Before writing ANY PHP code.

**Covers:** Class imports, Data classes for JSON columns, type hints, named arguments, PHPDoc patterns.

**Read now:** `.claude/rules/backend/php-conventions.md`

---

### 2. `controller-conventions.md`

**When to read:** Before creating or modifying controllers, API endpoints, or Inertia pages.

**Covers:** Data classes for Inertia props, Data classes for API responses, validation patterns, query optimization.

**Read now:** `.claude/rules/backend/controller-conventions.md`

---

### 3. `form-data-classes.md`

**When to read:** Before creating or modifying forms (create/edit functionality).

**Covers:** Form Data Classes pattern (FormContextData, FormRequestData, DetailsData), single form components for create/edit.

**Read now:** `.claude/rules/backend/form-data-classes.md`

---

### 4. `naming-conventions.md`

**When to read:** Before creating any new Data classes or Request classes.

**Covers:** Domain-specific naming, avoiding generic names, naming patterns for Data classes.

**Read now:** `.claude/rules/backend/naming-conventions.md`

---

### 5. `database-conventions.md`

**When to read:** Before creating migrations or modifying database schema.

**Covers:** Migration patterns, indexes, foreign keys, database design principles.

**Read now:** `.claude/rules/backend/database-conventions.md`

---

### 6. `testing-conventions.md`

**When to read:** Before writing tests.

**Covers:** Test structure, factory usage, assertion patterns, test database patterns.

**Read now:** `.claude/rules/backend/testing-conventions.md`

---

### 7. Data Class Rules (in different directory)

**When to read:** Before creating any Spatie Laravel Data classes.

**Covers:** When to create Data classes, constructor property promotion, validation annotations, Collection vs Array.

**Read now:** `.claude/rules/dataclasses/laravel-data.md`

---

## Quick Validation Checklist

Before submitting your code, scan for these red flags:

- [ ] Any `\Fully\Qualified\Names` in method bodies? → Should be imported at top
- [ ] Any `'column' => 'array'` in model casts? → Should use Data class
- [ ] Any `response()->json([...])` with raw arrays? → Should use Data class with `#[TypeScript()]`
- [ ] Any generic class names like `GetDataRequest` or `ConfigData`? → Should be domain-specific
- [ ] Any Data class properties outside constructor? → Should use constructor property promotion
- [ ] Missing `#[TypeScript()]` on Inertia Props or API Response Data classes?

**If you find any red flags, go back and read the relevant convention file.**

---

## How to Use

1. Identify what you're working on (controller, form, test, etc.)
2. Read the relevant convention files listed above
3. Write your code following the conventions
4. Run the validation checklist before submitting

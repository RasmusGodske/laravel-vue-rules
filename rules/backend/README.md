---
paths: app/**/*.php
---

# Backend Development Rules

These rules are **automatically loaded** when working on PHP files in `app/`.

## Available Rules

| Rule File | Loaded For | Covers |
|-----------|------------|--------|
| `php-conventions.md` | `app/**/*.php` | Class imports, type hints, named arguments, PHPDoc patterns |
| `http-context-dependencies.md` | `app/**/*.php` | Why `request()`, `auth()`, `session()` should never be used outside controllers |
| `controller-conventions.md` | `app/Http/Controllers/**/*.php` | Inertia props, API responses, validation |
| `controller-responses.md` | `app/Http/Controllers/**/*.php` | When to use Inertia vs JSON responses |
| `form-data-classes.md` | `app/Data/**/*.php`, `app/Http/Controllers/**/*.php` | Form Data Classes pattern |
| `naming-conventions.md` | `app/**/*.php` | Domain-specific naming for Data classes |
| `database-conventions.md` | `database/migrations/*.php` | Migration patterns, indexes, foreign keys |
| `testing-conventions.md` | `tests/**/*.php` | Test structure, factory usage, assertions |

**See also:** `../principles/` for cross-cutting rules that apply to both backend and frontend.

## Quick Validation Checklist

Before submitting your code, scan for these red flags:

- [ ] Any `\Fully\Qualified\Names` in method bodies? → Should be imported at top
- [ ] Any `'column' => 'array'` in model casts? → Should use Data class
- [ ] Any `response()->json([...])` with raw arrays? → Should use Data class with `#[TypeScript()]`
- [ ] Any generic class names like `GetDataRequest` or `ConfigData`? → Should be domain-specific
- [ ] Any Data class properties outside constructor? → Should use constructor property promotion
- [ ] Missing `#[TypeScript()]` on Inertia Props or API Response Data classes?
- [ ] Any `request()`, `auth()`, or `session()` outside controllers? → Pass explicitly as parameters
- [ ] Any cascading fallback chains when configuration is set? → Should fail explicitly (see `../principles/`)

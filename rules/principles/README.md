# Cross-Cutting Principles

These rules apply across the entire codebase - both backend (PHP) and frontend (TypeScript/Vue).

## Available Rules

| Rule File | Loaded For | Covers |
|-----------|------------|--------|
| `simple-predictable-workflows.md` | `app/**/*.php`, `resources/js/**/*.{vue,ts,tsx}` | Avoiding cascading fallbacks, explicit failure modes |
| `explicit-names.md` | `app/**/*.php`, `resources/js/**/*.{vue,ts,tsx}`, `tests/**/*.php`, `e2e/**/*.ts` | Variables named after their declared type, methods start with their action, no abbreviations: one line read on its own says what happens |

## Why These Are Separate

Some principles are language-agnostic and apply regardless of whether you're writing PHP or TypeScript. Rather than duplicating rules in both `backend/` and `frontend/`, cross-cutting concerns live here.

## Key Principles

1. **Simple, Predictable Workflows** - Code should follow one clear path. Configured options must be honored or fail explicitly - never cascade through alternatives silently.
2. **Explicit Names** - A variable or property is named after the type it is declared as; a method starts with its action. Long names are fine: one line read on its own should say what happens.

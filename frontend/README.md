# Frontend Development Rules

**⚠️ MANDATORY:** Read this file before writing any frontend code.

---

## Required Convention Files

Read these files in order based on what you're working on. Each file states when it should be read.

### 1. `vue-conventions.md` - READ FIRST

**When to read:** Before creating or modifying any Vue components.

**Covers:** `defineModel()` (NOT modelValue), TypeScript with Vue, Composition API patterns, component structure, props and emits.

**Read now:** `.claude/rules/frontend/vue-conventions.md`

---

### 2. `component-composition.md`

**When to read:** Before creating new components or when refactoring large components.

**Covers:** Component size limits, single responsibility principle, composition patterns, when to split components.

**Read now:** `.claude/rules/frontend/component-composition.md`

---

## Quick Validation Checklist

Before submitting your code, scan for these red flags:

- [ ] Using `modelValue` prop + manual `emit('update:modelValue')`? → Should use `defineModel()`
- [ ] Component template over 100-150 lines? → Should split into smaller components
- [ ] Component handling multiple responsibilities (data fetching + forms + display)? → Should split concerns
- [ ] Missing TypeScript types on props? → Should use `PropType<T>` or interfaces
- [ ] Using Options API (`export default { data(), methods: {} }`)? → Should use Composition API (`<script setup>`)
- [ ] Deeply nested template structure (5+ levels)? → Should extract child components

**If you find any red flags, go back and read the relevant convention file.**

---

## How to Use

1. Identify what you're working on (new component, refactoring, etc.)
2. Read the relevant convention files listed above
3. Write your code following the conventions
4. Run the validation checklist before submitting

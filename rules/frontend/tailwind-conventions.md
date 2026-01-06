---
paths:
  - "resources/js/**/*.{vue,ts,tsx}"
  - "resources/css/**/*.css"
  - "resources/views/**/*.blade.php"
---

# Tailwind CSS Conventions

## General Principles

- Use Tailwind CSS classes to style HTML
- Check and use existing Tailwind conventions within the project before writing your own
- Offer to extract repeated patterns into components that match the project's conventions
- Think through class placement, order, priority, and defaults
- Remove redundant classes, add classes to parent or child carefully to limit repetition
- Group elements logically
- Use the `search-docs` tool to get exact examples from the official documentation when needed

## Spacing

When listing items, use gap utilities for spacing, don't use margins:

```html
<!-- ✅ GOOD - Use gap -->
<div class="flex gap-8">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>

<!-- ❌ BAD - Don't use margins for spacing between siblings -->
<div class="flex">
    <div class="mr-8">Item 1</div>
    <div class="mr-8">Item 2</div>
    <div>Item 3</div>
</div>
```

## Dark Mode

If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:` variant.

```html
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
    Content that adapts to dark mode
</div>
```

## Tailwind v4 Specifics

This project uses **Tailwind CSS v4**. Key differences from v3:

### Import Syntax

```css
/* ✅ Tailwind v4 - Use CSS @import */
@import "tailwindcss";

/* ❌ Tailwind v3 - Don't use @tailwind directives */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### Deprecated Utilities (Don't Use)

Tailwind v4 removed deprecated utilities. Use the replacements:

| Deprecated | Replacement |
|------------|-------------|
| `bg-opacity-*` | `bg-black/50` (opacity in color) |
| `text-opacity-*` | `text-black/50` |
| `border-opacity-*` | `border-black/50` |
| `divide-opacity-*` | `divide-black/50` |
| `ring-opacity-*` | `ring-black/50` |
| `placeholder-opacity-*` | `placeholder-black/50` |
| `flex-shrink-*` | `shrink-*` |
| `flex-grow-*` | `grow-*` |
| `overflow-ellipsis` | `text-ellipsis` |
| `decoration-slice` | `box-decoration-slice` |
| `decoration-clone` | `box-decoration-clone` |

### Other v4 Changes

- `corePlugins` is not supported in Tailwind v4
- Opacity values are still numeric (e.g., `bg-black/50` for 50% opacity)

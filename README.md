# Laravel/Vue Convention Rules

Convention rules for Laravel/Vue projects, designed to be used with Claude Code's `.claude/rules/` system.

## Installation

```bash
composer require rasmusgodske/laravel-vue-rules --dev
```

## Usage

### Install rules for the first time

```bash
php artisan techstack-rules:update
```

This will copy the rules to `.claude/rules/techstack/` in your project.

### Update existing rules

```bash
php artisan techstack-rules:update --force
```

**Warning**: Using `--force` will overwrite any customizations you have made to the rules.

### Custom installation path

```bash
php artisan techstack-rules:update --path=.claude/rules/custom-name
```

## What's Included

### Backend Rules (`backend/`)

- **php-conventions.md** - Class imports, type hints, named arguments
- **controller-conventions.md** - Controller patterns, API responses, validation
- **controller-responses.md** - When to use Inertia vs JSON responses
- **form-data-classes.md** - Form Request patterns with Data classes
- **naming-conventions.md** - Domain-specific naming conventions
- **database-conventions.md** - Migration patterns, indexes, foreign keys
- **testing-conventions.md** - Test structure, factory usage, assertions

### Frontend Rules (`frontend/`)

- **vue-conventions.md** - Vue 3 Composition API, TypeScript, component structure
- **component-composition.md** - Component splitting, reusability, naming

### Data Class Rules (`dataclasses/`)

- **laravel-data.md** - Spatie Laravel Data patterns, validation, TypeScript export
- **custom-validation-rules.md** - Custom validation rules and patterns

## Path-Based Auto-Loading

All rules use path-based frontmatter for automatic loading. For example, backend rules only load when working with PHP files:

```yaml
---
paths: app/**/*.php
---
```

This means Claude Code automatically loads relevant rules based on what files you're editing.

## Customization

These rules are designed as starting templates. To customize:

1. Install the rules: `php artisan techstack-rules:update`
2. Edit the files in `.claude/rules/techstack/` to match your conventions
3. Commit the customized rules to your project

**Note**: Running `techstack-rules:update --force` again will overwrite your customizations. Consider versioning your customized rules in your project.

## Philosophy

Rules follow these principles:

- **Example-driven** - Show good vs bad patterns
- **Modern** - Laravel 10+, Vue 3, PHP 8+
- **Concise** - Focus on "what" and "why", not "how to code"

## License

MIT License

## Author

**RasmusGodske** - [@RasmusGodske](https://github.com/RasmusGodske)

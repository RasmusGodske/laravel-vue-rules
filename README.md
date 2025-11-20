# Laravel/Vue Convention Rules

Convention rules for Laravel/Vue projects. These rules are designed to be used with [dev-agent-workflow](https://github.com/RasmusGodske/dev-agent-workflow) Claude Code plugins.

## What This Is

This repository contains starter convention files for the `.claude/rules/` directory structure:

- **backend/** - Backend conventions (PHP, Laravel, Data classes)
- **frontend/** - Frontend conventions (Vue.js, Components)
- **dataclasses/** - Spatie Laravel Data patterns
- **documentation/** - Documentation structure and writing conventions
- **linear/** - Linear project management conventions

## Usage

### Quick Setup (via rules-boilerplate plugin)

```bash
# Install the rules-boilerplate plugin
/plugin marketplace add https://github.com/RasmusGodske/dev-agent-workflow
/plugin install rules-boilerplate@dev-agent-workflow

# Run setup (will clone this repo into .claude/rules/)
/setup-rules
```

### Manual Setup (Git Submodule)

```bash
# In your Laravel project root
git submodule add https://github.com/RasmusGodske/laravel-vue-rules .claude/rules

# Or clone directly
git clone https://github.com/RasmusGodske/laravel-vue-rules .claude/rules
```

### Update Rules

```bash
# Pull latest rules
cd .claude/rules
git pull origin main
```

## Customization

These are **starter templates**. You should:

1. **Fork this repository** for your team/organization
2. **Customize the rules** to match your specific conventions
3. **Use your fork** in projects via submodule or the plugin

## What's Included

### Backend Rules

- **form-data-classes.md** - When to use Data classes vs arrays, Form Request patterns
- **controller-conventions.md** - Controller patterns, API responses, validation
- **database-conventions.md** - Migration patterns, indexes, foreign keys
- **testing-conventions.md** - Test structure, factory usage, assertions
- **php-conventions.md** - Class imports, type hints, named arguments

### Frontend Rules

- **vue-conventions.md** - Vue 3 Composition API, TypeScript, component structure
- **component-composition.md** - Component splitting, reusability, naming

### Data Class Rules

- **laravel-data.md** - Spatie Laravel Data patterns, validation, TypeScript export

### Documentation Rules

- **README.md** - Documentation structure (domains, layers), file-to-doc mapping, templates, style guidelines

### Linear Rules

- **project-conventions.md** - Linear project structure and management conventions

## Integration with dev-agent-workflow

These rules are automatically loaded when you activate roles:

```bash
/roles/backend-engineer  # Loads backend/ rules
/roles/frontend-engineer # Loads frontend/ rules
/roles/fullstack-engineer # Loads both
```

## Philosophy

Rules follow these principles:
- **Example-driven** - Show good vs bad patterns
- **Modern** - Laravel 10+, Vue 3, PHP 8+
- **Generic** - Easy to customize for any project
- **Concise** - Focus on "what" and "why", not "how to code"

## Versioning

- Use git tags for versions
- Fork for project-specific rules
- Submit PRs for improvements

## Contributing

Have better conventions? PRs welcome!

1. Fork the repository
2. Update rule files with examples
3. Test with a Laravel/Vue project
4. Submit pull request

## License

MIT License

## Author

**RasmusGodske**
- GitHub: [@RasmusGodske](https://github.com/RasmusGodske)

## Related Projects

- [dev-agent-workflow](https://github.com/RasmusGodske/dev-agent-workflow) - Claude Code plugins that use these rules

# Documentation Conventions

**⚠️ MANDATORY:** Read this file before writing any project documentation.

---

## Overview

This project uses a **research-driven documentation system** organized by business domains and technical layers. All documentation lives in the `docs/` directory and follows predictable conventions.

---

## Convention Files

Read these files based on what you're doing:

### For Research Agent

**When to read:** Before researching and identifying documentation gaps.

- **`structure.md`** - Documentation organization (domains, layers, directories)
- **`file-mapping.md`** - Code-to-doc mapping conventions (where docs should live)

### For Documentation Writers

**When to read:** Before creating or updating documentation.

- **`structure.md`** - Documentation organization
- **`file-mapping.md`** - Where to place documentation
- **`templates.md`** - Templates for all documentation types
- **`writing-style.md`** - Style guidelines and best practices
- **`index-maintenance.md`** - How to maintain INDEX.md
- **`common-mistakes.md`** - Validation checklist and things to avoid

### For Understanding the System

**When to read:** To learn how the research-driven workflow works.

- **`research-workflow.md`** - How research agent and documentation generation work together

---

## Quick Reference

### Documentation Structure

```
docs/
├── INDEX.md                 # Master index
├── domains/                 # Business domains
│   └── {domain}/
│       ├── README.md
│       └── features/
└── layers/                  # Technical layers
    ├── backend/
    └── frontend/
```

### Key Principles

1. **Single source of truth** - Conventions defined once in `.claude/rules/documentation/`
2. **Domains for business** - User-facing features and capabilities
3. **Layers for technical** - Implementation details (services, components)
4. **Always update INDEX.md** - Critical for discoverability
5. **Use templates** - Consistency across all documentation

### Common Tasks

| Task | Files to Read |
|------|---------------|
| Research codebase gaps | `structure.md`, `file-mapping.md` |
| Write feature documentation | `templates.md`, `writing-style.md` |
| Document a service/component | `file-mapping.md`, `templates.md` |
| Update INDEX.md | `index-maintenance.md` |
| Check if docs are correct | `common-mistakes.md` |

---

## File Descriptions

- **`structure.md`** - How documentation is organized (domains, layers, _research)
- **`file-mapping.md`** - Code path → documentation path conventions
- **`templates.md`** - Domain README, feature, and component templates
- **`writing-style.md`** - Clarity, structure, completeness guidelines
- **`index-maintenance.md`** - INDEX.md format and update rules
- **`common-mistakes.md`** - What to avoid, validation checklist
- **`research-workflow.md`** - Research agent lifecycle and report processing

---

## Getting Started

**New to the documentation system?**

1. Read `structure.md` - Understand the organization
2. Read `file-mapping.md` - Know where things go
3. Read `templates.md` - See examples of good documentation
4. Use the checklist in `common-mistakes.md` before submitting

**Using the research agent?**

1. Read `structure.md` and `file-mapping.md`
2. Understand `research-workflow.md`
3. Research agent creates reports automatically
4. Use `/docs/process-documentation-reports` to generate docs

---

## Integration with Code Conventions

Documentation conventions work alongside code conventions:

- **Backend Code:** `.claude/rules/backend/*.md`
- **Frontend Code:** `.claude/rules/frontend/*.md`
- **Data Classes:** `.claude/rules/dataclasses/*.md`
- **Documentation:** `.claude/rules/documentation/*.md` (this directory)

When writing **code**, focus on implementation details in comments.
When writing **docs**, focus on architecture and design decisions.

# Research-Driven Documentation Workflow

This file explains how the research-driven documentation system works and how to use it.

---

## Overview

This project uses an automated research workflow to identify and address documentation gaps:

1. **Research agent** explores the codebase when context is needed
2. **Gap reports** are created automatically
3. **Documentation agent** processes reports and generates documentation
4. **Documentation** follows conventions defined in this directory

---

## Workflow Diagram

```
Developer working
      ↓
Claude needs context
      ↓
Invokes research-agent ← [separate context]
      ↓
Research agent:
  - Reads conventions (structure.md, file-mapping.md)
  - Searches existing documentation
  - Searches codebase
  - Creates report in docs/_research/lacking/pending/
  - Returns summary to main Claude
      ↓
Main Claude continues work with context
      ↓
[Later] Developer runs /docs/process-documentation-reports
      ↓
Documentation agent:
  - Reads pending reports
  - Loads conventions (all .md files in this directory)
  - Creates plan
  - Gets user approval
  - Generates documentation
  - Updates INDEX.md
  - Moves report to processed/
```

---

## Research Agent

### Purpose

The research agent's job is to **observe and report**, not prescribe solutions.

### What It Does

1. **Loads conventions** from `structure.md` and `file-mapping.md`
2. **Searches systematically:**
   - docs/INDEX.md for quick lookup
   - Expected domain/layer locations
   - Codebase for implementation
3. **Creates two artifacts:**
   - **Detailed report** - Full findings for documentation agent
   - **Concise summary** - Quick overview for main Claude

### Report Structure

Reports are stored in:
```
docs/_research/lacking/pending/{timestamp}_{slug}/
├── report.md     # Detailed findings
```

Summaries are stored in:
```
docs/_research/summaries/{timestamp}_{slug}/
└── summary.md    # Concise overview
```

### Report Format

```markdown
---
topic: Topic Name
priority: high|medium|low
created: 2025-11-20T14:30:22Z
requested_for: Context for why this was researched
---

# Research Report: Topic Name

## What Was Requested
Why was this research needed?

## Where I Looked
Complete list of files checked

## What I Found
- Existing documentation
- Code implementation
- Test coverage

## Things I'm Unsure About
Honest gaps in understanding

## What Could Be Improved
Observations about documentation gaps
```

### Key Principles

- **Observational** - Reports what IS, not what SHOULD BE
- **Systematic** - Lists all files checked
- **Honest** - Notes uncertainties
- **Detailed** - Provides enough context for decision-making

---

## Documentation Processing

### Command

`/docs/process-documentation-reports`

### What It Does

1. **Finds pending reports** in `docs/_research/lacking/pending/`
2. **For each report:**
   - Reads the report
   - Loads documentation conventions
   - Verifies findings
   - Creates a plan
   - Shows plan to user
   - Gets approval
   - Generates documentation
   - Updates INDEX.md
   - Creates resolution
   - Moves to processed/

### Report Lifecycle

```
pending/              # New reports awaiting review
    ↓
in-progress/          # Currently being processed
    ↓
processed/            # Completed (with resolution)
```

### Files in Each Stage

**pending/ directory:**
```
{timestamp}_{slug}/
└── report.md
```

**in-progress/ directory:**
```
{timestamp}_{slug}/
├── report.md
└── plan.md          # Generated: what will be done
```

**processed/ directory:**
```
{timestamp}_{slug}/
├── report.md
├── plan.md
└── resolution.md    # Generated: what was done
```

---

## Priority Levels

Reports have priority levels to help with processing order:

- **high** - Needed for current work (blocks development)
- **medium** - Would improve understanding (helpful but not blocking)
- **low** - Nice-to-have improvement

Priority is set by the research agent based on context.

---

## Conventions Loading

### Research Agent Loads

**Files:**
- `structure.md` - Where to search
- `file-mapping.md` - Where docs should be

**Why:**
- Needs to know where to look
- Needs to know where docs should exist
- Doesn't need writing guidelines (it doesn't write)

### Documentation Agent Loads

**Files:**
- ALL files in `.claude/rules/documentation/`

**Why:**
- Needs complete understanding of conventions
- Must follow templates
- Must maintain quality standards
- Must update INDEX.md correctly

---

## User Interaction Points

### 1. Research Agent Invocation

**When:** Automatically when Claude needs context

**User sees:**
```
Research complete.

Summary: docs/_research/summaries/2025-11-20_143022_auth-jwt-validation/summary.md
Full report: docs/_research/lacking/pending/2025-11-20_143022_auth-jwt-validation/report.md

Found:
- docs/domains/authentication/README.md mentions JWT briefly

Missing:
- No dedicated JWT validation documentation

Key code: app/Services/Auth/JwtValidator.php:45
```

**User action:** None required - Claude continues with context

### 2. Report Processing

**When:** Developer runs `/docs/process-documentation-reports`

**User sees:**
```
Found 2 pending documentation reports:
1. 2025-11-20_143022_auth-jwt-validation - Authentication JWT Validation
2. 2025-11-20_150135_payment-flow - Payment Processing Flow

Process these reports? [yes/no]
```

**User action:** Choose yes/no

### 3. Plan Approval

**User sees:**
```
Report: Authentication JWT Validation (priority: high)

I plan to:
- Create docs/domains/authentication/features/jwt-validation.md
- Update docs/domains/authentication/README.md
- Create docs/layers/backend/services/auth-service.md
- Update docs/INDEX.md (3 new entries)

Details in: docs/_research/lacking/2025-11-20_143022_auth-jwt-validation/plan.md

Proceed with this plan? [yes/no/skip]
```

**User action:** Review plan and approve/reject/skip

### 4. Completion

**User sees:**
```
✅ Processed: Authentication JWT Validation
   Created: 2 files
   Updated: 2 files

1 report remaining.
```

---

## Benefits of This System

### For Developers

- **Less repetitive explaining** - Context gathering is automated
- **Documentation gaps visible** - Reports show what's missing
- **Continuous improvement** - Documentation grows over time
- **Non-blocking** - Can process reports when convenient

### For Projects

- **Self-documenting** - Gaps are identified automatically
- **Consistent quality** - All docs follow conventions
- **Scalable** - Works for small and large projects
- **Maintainable** - Single source of truth for conventions

### For Claude Code

- **Focused research** - Research agent has clean context
- **Better context** - Primary Claude gets exactly what it needs
- **Quality assurance** - Documentation follows standards

---

## Configuration and Customization

### Adjusting Conventions

To customize for your project:

1. **Fork laravel-vue-rules repository**
2. **Modify convention files** in `documentation/` directory
3. **Update your `.claude/rules/`** to point to your fork

### Adding Custom Sections

You can extend templates in `templates.md`:

```markdown
## Custom Section (if applicable)
Your project-specific section
```

All documentation following the template will then include this section.

### Gitignore Considerations

**Option 1: Commit research artifacts**
- Pro: Historical record of documentation evolution
- Con: Clutters git history

**Option 2: Gitignore `docs/_research/`**
- Pro: Clean git history
- Con: Lose documentation improvement tracking

```gitignore
# Option 2: Clean git history
docs/_research/
```

---

## Best Practices

### When to Run Processing

**Good times:**
- End of feature development
- Before code review
- After significant architectural changes
- Weekly documentation maintenance

**Don't:**
- Process reports while actively coding (unless blocking)
- Batch too many reports at once (overwhelming)

### Quality Over Quantity

**Prefer:**
- ✅ Processing 2-3 reports thoroughly
- ✅ Reviewing plans carefully
- ✅ Ensuring high-quality documentation

**Avoid:**
- ❌ Rushing through many reports
- ❌ Auto-approving without review
- ❌ Generating low-quality docs for completion's sake

### Maintenance

**Regular tasks:**
- Process pending reports weekly
- Review and prune old processed reports (optional)
- Update conventions as project evolves
- Verify INDEX.md stays current

---

## Troubleshooting

### Research Agent Not Finding Docs

**Possible causes:**
- Docs exist but not in expected locations
- Docs not in INDEX.md
- File naming doesn't follow conventions

**Solutions:**
- Review `file-mapping.md` conventions
- Ensure INDEX.md is up to date
- Check file naming (lowercase-with-hyphens)

### Generated Documentation is Low Quality

**Possible causes:**
- Conventions are vague
- Research report lacked detail
- Templates need improvement

**Solutions:**
- Enhance convention files
- Manually improve generated docs
- Update templates for future docs

### Too Many Pending Reports

**Causes:**
- Active development creating many gaps
- Haven't processed reports recently

**Solutions:**
- Prioritize high-priority reports
- Process in batches
- Consider if all reports warrant documentation

---

## Related Files

- **structure.md** - Documentation organization
- **file-mapping.md** - Where docs should be placed
- **templates.md** - What structure to use
- **writing-style.md** - How to write quality docs
- **index-maintenance.md** - Keeping docs discoverable
- **common-mistakes.md** - What to avoid

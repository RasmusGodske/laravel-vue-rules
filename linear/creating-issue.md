# Creating Linear Issues

**When to read:** Before writing individual Linear issues for development work.

---

## Purpose

Issues describe **specific problems to solve** with enough context for Claude Code agents to work autonomously.

Issues are written for agents that have:
- All conventions from `.claude/rules/backend/` or `.claude/rules/frontend/`
- `CLAUDE.md` with project context
- The Linear project description
- Backend/Frontend reviewer subagents

**Therefore:** Focus on **domain knowledge and outcomes**, not implementation details or repeated conventions.

---

## What to Include

### ✅ DO Include

#### 1. Outcome-focused problem statement

Describe what's broken/missing and why it matters.

**Example:**
```markdown
✅ "Enable display config validation for money columns"
✅ "Add distance-based filtering for user queries"

❌ "Add MONEY case to validateDisplayConfigTypes() method"
❌ "Create a new MoneyFilterApplicator class"
```

#### 2. Code snippets showing the problem (when applicable)

If you know exactly where the bug is, show it with inline comments.

**Example:**
```markdown
**Current validation logic:**
```php
// app/Services/ObjectDefinition/ObjectDefinitionService.php:121-126
$isValid = match ($columnTypeValue) {
    'decimal', 'integer' => $displayType === 'number',
    'boolean' => $displayType === 'boolean',
    'string', 'text' => $displayType === 'string',
    default => false, // ❌ MONEY type falls into this default case
};
```
```

#### 3. Domain context

Provide specific knowledge needed for THIS problem.

**Example:**
```markdown
✅ "Money columns use NumberCurrencyFormat display config (similar to decimal/integer)"
✅ "Valid coordinate ranges: Latitude -90 to +90, Longitude -180 to +180"
✅ "Geolocation data includes pre-computed geohash for efficient proximity queries"
```

#### 4. Clear acceptance criteria

Define testable outcomes that indicate "done".

**Example:**
```markdown
## Acceptance Criteria
- [ ] Money columns with NumberCurrencyFormat display_config save successfully
- [ ] Validation ensures money columns only accept number format types
- [ ] Tests verify validation behavior
- [ ] No regression for existing column types
```

#### 5. Helpful references

Point to similar patterns, documentation, or related code.

**Example:**
```markdown
## Helpful References
- See how decimal columns handle display_config validation
- Similar pattern: `DateFilterApplicator` for range queries
- Documentation: `docs/ObjectDefinition/money-column-type.md`
- Related validation: `app/Validators/CoordinateValidator.php`
```

#### 6. Edge cases (if applicable)

Call out gotchas or non-obvious constraints.

**Example:**
```markdown
## Edge Cases
- Handle queries crossing the antimeridian (±180° longitude)
- Ensure money columns only accept NumberCurrencyFormat, not other display types
- Consider NULL values in inequality comparisons
```

#### 7. Dependencies (if applicable)

Explain what this issue depends on and why.

**Example:**
```markdown
## Dependencies
Depends on RAS-123 (geohash index creation) because we need the index
for query performance with large datasets.
```

---

### ❌ DON'T Include

#### 1. Implementation prescriptions

Don't specify method names, class structures, or step-by-step instructions.

**Example:**
```markdown
❌ "Create a new method called validateMoneyDisplayConfig()"
❌ "In line 125, add: case 'money' => $displayType === 'number'"
❌ "Step 1: Open the file. Step 2: Find the method. Step 3: Add the case."
```

#### 2. Repeated conventions

Agent already knows from `.claude/rules/backend` or `.claude/rules/frontend`.

**Example:**
```markdown
❌ "Follow backend conventions for naming"
❌ "Write tests using Pest"
❌ "Use named arguments in method calls"
❌ "Import classes at the top of the file"
```

#### 3. Unnecessary file paths

Only include paths if it helps locate a specific bug. Agent can search.

**Example:**
```markdown
❌ "Modify app/Services/ObjectDefinition/ObjectDefinitionService.php"
❌ "Create tests in tests/Feature/Services/ObjectDefinition/"

✅ "See current bug in ObjectDefinitionService.php:121" (with code snippet)
```

---

## Template

```markdown
# [Outcome-Focused Title]

## Problem
[1-2 sentences: What's broken/missing and why it matters]

[OPTIONAL: Code snippet showing the problem with inline comments]

## Context
[Domain knowledge specific to this problem]
[Reference project description if needed]

## Acceptance Criteria
- [ ] [Testable outcome 1]
- [ ] [Testable outcome 2]
- [ ] [Testable outcome 3]

## Edge Cases
[Only if applicable]

## Helpful References
- [Similar patterns to reference]
- [Relevant documentation]
- [Related code]

## Dependencies
[Only if blocked: "Depends on RAS-XX because..."]
```

---

## Examples

See `.claude/rules/linear/examples/issue-examples.md` for multiple examples:
- Bug fix
- New feature
- Testing
- Refactoring

---

## Common Patterns

### Bug Fix Issue

**Focus on:** What's broken, why it matters, where the bug is

**Include:** Code snippet with inline comment marking the problem

**Example title:** "Fix validation for invalid coordinates"

---

### New Feature Issue

**Focus on:** What capability is missing, why users need it, what success looks like

**Include:** Domain context, edge cases, helpful references

**Example title:** "Add distance-based filtering for user queries"

---

### Testing Issue

**Focus on:** What's untested, why testing is important, what scenarios to cover

**Include:** List of test scenarios, reference to similar test patterns

**Example title:** "Add integration tests for geocoding service"

---

### Refactoring Issue

**Focus on:** What's duplicated/complex, why it needs refactoring, what the outcome is

**Include:** Current duplication/complexity, desired structure

**Example title:** "Extract coordinate validation into reusable validator"

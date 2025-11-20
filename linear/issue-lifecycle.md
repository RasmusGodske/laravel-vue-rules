# Working with Linear Issues

**When to read:** Before starting work on a Linear issue.

---

## Starting an Issue

### 1. Read the project description first

Before diving into the specific issue, understand the broader feature context:
- What problem is this feature solving?
- How does the architecture work?
- What areas are affected?
- What are known limitations?

**Why:** This prevents misunderstanding the issue in isolation.

---

### 2. Read the issue description

Carefully review:
- The specific problem to solve
- Acceptance criteria (what "done" looks like)
- Edge cases to watch for
- Helpful references
- Dependencies (is this blocked by other issues?)

---

### 3. Update issue status

Before starting work:
1. Move issue to **"In Progress"**
2. Assign to yourself
3. Check the project description one more time

---

## During Development

### When to Update the Issue

**DO update when:**

✅ **You discover unexpected blockers**
```markdown
Comment: "Blocked: The geocoding API doesn't support the format we need.
Investigating alternative approaches."

Update status to "Blocked"
```

✅ **You find the scope is larger than described**
```markdown
Comment: "Discovered that UserService, TeamService, and OrganizationService
all need the same change. Should we refactor this into a shared validator first?"
```

✅ **You need clarification from the team**
```markdown
Comment: "The acceptance criteria says 'validate coordinates' but doesn't specify
if we should accept coordinates outside the standard ±180/±90 range for edge cases.
Should we reject or normalize them?"
```

✅ **You find related bugs or edge cases**
```markdown
Comment: "While implementing, discovered that existing NULL coordinates also
break map rendering. Should I fix this in the same issue or create a separate one?"
```

---

**DON'T update for:**

❌ Normal progress updates
```markdown
❌ "Working on the validation logic"
❌ "Tests are passing"
❌ "50% complete"
```

❌ Micro-decisions during implementation
```markdown
❌ "Decided to use match statement instead of if/else"
❌ "Extracting validation into a private method"
```

❌ Things already covered in acceptance criteria
```markdown
❌ "Added tests" (if acceptance criteria already says to add tests)
```

---

### Commenting on Issues

Add comments when:

**Asking questions:**
```markdown
"Should coordinate validation accept NULL values for optional fields?"
```

**Noting discovered edge cases:**
```markdown
"Found that queries crossing the antimeridian (±180° longitude) need
special handling. Adding this to the implementation."
```

**Explaining non-obvious decisions:**
```markdown
"Using geohash bounding box instead of direct distance calculation
for performance reasons (10x faster with large datasets)."
```

**Linking to related issues:**
```markdown
"Related to RAS-456 (coordinate normalization). May want to combine these."
```

---

## Completing an Issue

### Before Marking "Done"

Run through this checklist:

- [ ] **All acceptance criteria met**
  - Don't skip any criteria
  - Each checkbox should be verifiable

- [ ] **Tests written and passing**
  - Unit tests for new logic
  - Integration tests if touching multiple components
  - Edge cases covered

- [ ] **Code reviewed**
  - If using Claude Code: Backend/Frontend reviewer subagent ran
  - If human: Code review completed
  - Feedback addressed

- [ ] **No regressions introduced**
  - Existing tests still pass
  - Related functionality still works

- [ ] **Documentation updated** (if applicable)
  - User-facing docs updated
  - Technical docs updated
  - Code comments added for complex logic

---

### Final Steps

#### 1. Add completion comment (optional but helpful)

Especially useful if:
- You deviated from the original plan
- You discovered edge cases
- You created follow-up issues

**Example:**
```markdown
## Completion Summary

**Changes made:**
- Added MONEY case to display config validation
- Updated validation to ensure NumberCurrencyFormat type
- Added tests covering all 6 column types with display configs

**Edge cases handled:**
- Validation rejects non-number format types for money columns
- NULL display_config still allowed (uses default)

**Follow-up issues created:**
- RAS-789: Add similar validation for SELECT column type
```

---

#### 2. Update issue status to "Done"

Move the issue to the "Done" status.

---

#### 3. Update dependent issues

If other issues were blocked by this one:
1. Find issues with "Depends on RAS-XXX"
2. Comment on them: "RAS-XXX is complete, this issue is unblocked"
3. Remove "Blocked" status if applicable

---

## Issue States

| State | Meaning | When to Use |
|-------|---------|-------------|
| **Backlog** | Not yet prioritized | Default state for new issues |
| **Todo** | Ready to work on | Prioritized and ready to start |
| **In Progress** | Actively being worked on | You've started implementation |
| **Done** | Completed and tested | All acceptance criteria met |
| **Blocked** | Waiting on dependency or clarification | Can't proceed without something |

---

## Handling Blocked Issues

If you're blocked:

1. **Update status to "Blocked"**

2. **Add comment explaining why:**
```markdown
Blocked: Depends on RAS-456 (geocoding API integration) because we need
the API response format to validate coordinates correctly.
```

3. **Tag relevant people** (if clarification needed)

4. **Move to next issue** (don't wait idle)

---

## Creating Follow-up Issues

If you discover new work during implementation:

**When to create follow-up:**
- Refactoring opportunities (not critical for current issue)
- Related bugs in other areas
- Additional edge cases not in original scope
- Performance optimizations

**When to expand current issue:**
- Critical bugs discovered in the same area
- Edge cases directly related to acceptance criteria
- Necessary refactoring for the current issue to work

**Example follow-up:**
```markdown
Created RAS-890: "Optimize geohash index for polar regions"

During implementation of RAS-789, discovered that geohash precision
degrades near poles. Not blocking this issue, but should be optimized
for production use.
```

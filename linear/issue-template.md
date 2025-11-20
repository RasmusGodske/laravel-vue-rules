# Issue Template

**When to read:** When you need a quick copy-paste template for creating an issue.

---

## Template

Copy and paste this template, then fill in the sections:

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
[Only if applicable - things to watch out for]

## Helpful References
- [Similar patterns to reference]
- [Relevant documentation]
- [Related code]

## Dependencies
[Only if blocked: "Depends on RAS-XX because..."]
```

---

## Usage Guidelines

### Title

**Good titles:**
- ✅ "Enable display config validation for money columns"
- ✅ "Add distance-based filtering for user queries"
- ✅ "Fix coordinate validation to handle NULL values"

**Bad titles:**
- ❌ "Update ObjectDefinitionService"
- ❌ "Add MONEY case"
- ❌ "Fix bug in validation"

**Pattern:** "[Action] [Outcome/Capability]"

---

### Problem Section

**What to include:**
- What's broken or missing (1 sentence)
- Why it matters / user impact (1 sentence)
- Code snippet if you know exact location of bug

**Example:**
```markdown
## Problem
Money columns cannot be saved with display_config because ObjectDefinitionService
validation doesn't recognize the MONEY column type. This blocks users from
configuring how money values display in tables and reports.

**Current validation logic:**
```php
// app/Services/ObjectDefinition/ObjectDefinitionService.php:121
$isValid = match ($columnTypeValue) {
    'decimal', 'integer' => $displayType === 'number',
    default => false, // ❌ MONEY falls here
};
```
```

---

### Context Section

**What to include:**
- Domain knowledge agent needs
- Reference to project description
- Key architectural points

**Example:**
```markdown
## Context
Money columns use NumberCurrencyFormat display config (similar to how decimal and
integer columns support number formats). See project description for money column
architecture details.

The validation should ensure money columns only accept number-type display configs,
not string or boolean formats.
```

---

### Acceptance Criteria Section

**What to include:**
- Testable outcomes (can verify with "yes/no")
- Use checkboxes for each criterion
- 3-5 criteria is typical

**Example:**
```markdown
## Acceptance Criteria
- [ ] Money columns with NumberCurrencyFormat display_config save successfully
- [ ] Validation rejects non-number format types for money columns
- [ ] Tests verify validation behavior for all column types
- [ ] No regression in existing display config validation
```

---

### Edge Cases Section

**What to include:**
- Only include if there are non-obvious gotchas
- Leave empty or remove if not applicable

**Example:**
```markdown
## Edge Cases
- Handle queries crossing the antimeridian (±180° longitude)
- Ensure NULL values in coordinates don't break map rendering
- Consider precision loss when converting between coordinate systems
```

---

### Helpful References Section

**What to include:**
- Similar code patterns to reference
- Relevant documentation
- Related issues or code

**Example:**
```markdown
## Helpful References
- See how decimal columns handle display_config in same method
- Documentation: `docs/ObjectDefinition/money-column-type.md`
- Similar validation: `app/Validators/CoordinateValidator.php`
- Related issue: RAS-456 (coordinate normalization)
```

---

### Dependencies Section

**What to include:**
- Only include if this issue is blocked by another
- Explain WHY it's blocked, not just that it is

**Example:**
```markdown
## Dependencies
Depends on RAS-456 (geohash index creation) because we need the database
index for performant proximity queries with large datasets (100k+ records).
```

---

## Quick Examples

### Bug Fix

```markdown
# Fix validation for invalid geolocation coordinates

## Problem
User profiles can be saved with invalid coordinates (lat > 90, lng > 180),
breaking map displays and causing query errors.

**Current code:**
```php
// app/Services/UserService.php:145
$userData->location = $input['location']; // ❌ No validation
```

## Context
Valid coordinate ranges:
- Latitude: -90 to +90
- Longitude: -180 to +180

Invalid coordinates cause downstream map rendering failures.

## Acceptance Criteria
- [ ] Validation rejects lat outside [-90, 90]
- [ ] Validation rejects lng outside [-180, 180]
- [ ] Clear error messages for invalid coordinates
- [ ] Tests verify boundary conditions

## Helpful References
- See how date validation works in `DateColumnProcessor`
```

---

### New Feature

```markdown
# Add distance-based filtering for user queries

## Problem
Users cannot filter by proximity ("find all users within 50km of Copenhagen").
This blocks sales teams from finding nearby customers.

## Context
Geolocation data includes pre-computed geohash for efficient proximity queries.
Filter should support:
- Center point (lat, lng or address)
- Radius in km
- Result ordering by distance

## Acceptance Criteria
- [ ] Filter UI accepts center point and radius
- [ ] Backend converts to geohash bounding box query
- [ ] Results ordered by distance (nearest first)
- [ ] Tests verify distance calculations
- [ ] Performance acceptable for 100k+ records

## Edge Cases
- Handle queries crossing the antimeridian (±180° longitude)
- Handle polar regions (lat near ±90°)

## Helpful References
- Geohash proximity algorithm: `docs/geolocation/geohash-proximity.md`
- Similar pattern: Date range filters in `DateFilterApplicator`

## Dependencies
Depends on RAS-456 (geohash index creation) for query performance.
```

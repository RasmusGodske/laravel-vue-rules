# Creating Linear Project Descriptions

**When to read:** Before creating a new Linear project for a feature or initiative.

---

## Purpose

Project descriptions provide **broad feature context** so Claude Code agents understand the "why" and "how" without needing explanation in each issue.

Think of it as: **"What would I tell a new team member joining this project?"**

---

## Structure

### 1. Overview (What)

Brief 1-2 paragraph summary of the feature.

**Example:**
```markdown
## Overview
Introduces geolocation data type for user profiles, enabling location-based
features while maintaining privacy controls and efficient proximity searching.
```

---

### 2. Problem & Context (Why)

Explain what problem existed before and why this matters.

**Include:**
- What challenges existed in the old system
- What pain points users experienced
- Why this feature is valuable
- Business context

**Example:**
```markdown
## Problem & Context

**Previous challenge:**
Users stored addresses as simple text strings. This made it impossible to:
- Find users within a radius
- Sort by distance
- Display on maps

**User impact:**
- Sales teams couldn't find nearby customers
- Support couldn't route to local representatives
```

---

### 3. Architecture & How It Works (How)

Explain key technical decisions and how the feature works.

**Include:**
- Data structures and storage format
- Important patterns or conventions
- Key architectural decisions
- How different parts interact

**Example:**
```markdown
## Architecture & How It Works

**Data structure:**
```json
{
  "address": "123 Main St",
  "lat": 41.8781,
  "lng": -87.6298,
  "_computed": {
    "geohash": "dp3wm7zs"  // For efficient proximity queries
  }
}
```

**Key decisions:**
- Store both human-readable address and coordinates
- Use geohash for database indexing
- Geocoding happens on backend
```

---

### 4. Affected Areas (Where)

List systems/components that need changes or are impacted.

**This is critical** - it helps agents understand the full scope.

**Example:**
```markdown
## Affected Areas

**Backend:**
- **UserService** - Saving users with location data
- **Geocoding service** - Address → coordinates conversion
- **Query filters** - Distance-based filtering
- **Validation** - Coordinate bounds checking

**Frontend:**
- **Location input component** - Address autocomplete
- **Map display component** - Show user location
- **Filter UI** - Distance radius selector

**Database:**
- New geohash index for proximity queries
- Migration for existing user data

**Integrations:**
- Google Maps API for geocoding
- May need rate limiting
```

---

### 5. Documentation References

Point to detailed technical documentation.

**Example:**
```markdown
## Documentation References
- Technical details: `docs/geolocation/geohash-explainer.md`
- Privacy controls: `docs/geolocation/privacy-zones.md`
- API integration: `docs/geolocation/google-maps-api.md`
```

---

### 6. Known Limitations

Document what won't work or deliberate constraints.

**Example:**
```markdown
## Known Limitations
- Geocoding requires internet connection (no offline support)
- Historical addresses not geocoded (only new/updated ones)
- Max precision: ~10m (city-block level, not GPS-precise)
- Cannot compute geolocation columns (is_computed not supported)
```

---

## Depth Guidelines

**Too shallow:** Just restates the feature name
```markdown
❌ "We're adding support for money columns."
```

**Too deep:** Prescribes exact implementation
```markdown
❌ "In MoneyColumnProcessor.php, add a processAmount() method that
   takes $amount and $currency parameters and returns an array..."
```

**Just right:** Gives context and pointers, lets agent explore
```markdown
✅ "Money columns store amount + currency together with a pre-computed
   EUR value for cross-currency filtering. The processor validates
   currency and converts to EUR using exchange rates."
```

---

## Template

```markdown
# [Feature Name]

## Overview
[1-2 paragraph summary of the feature]

## Problem & Context

**Previous challenge:**
[What didn't work before]

**User impact:**
[How this affects users]

## Architecture & How It Works

**Data structure:**
[Show key data formats]

**Key decisions:**
[Important architectural choices]
[How components interact]

## Affected Areas

**Backend:**
- **Service/Component** - What needs to change

**Frontend:**
- **Component** - What needs to change

**Database:**
- [Schema changes]

**Integrations:**
- [External systems affected]

## Documentation References
- [Link to detailed docs]

## Known Limitations
- [What won't work]
- [Deliberate constraints]
```

---

## Mandatory Review

**🚨 CRITICAL: Before finalizing any project description, you MUST use the project-description-reviewer agent.**

After drafting your project description:

1. **Use the Task tool** to invoke the `project-description-reviewer` agent:
   ```
   Use the Task tool with:
   - description: "Review project description"
   - prompt: "Review the following project description: [paste your description]"
   - subagent_type: "project-description-reviewer"
   ```

2. **Address all feedback** from the reviewer

3. **Only then** create the Linear project

**You do NOT have discretion to skip review.** Even if the description seems complete, invoke the reviewer.

---

## Examples

See `.claude/rules/linear/examples/project-example.md` for a complete example.

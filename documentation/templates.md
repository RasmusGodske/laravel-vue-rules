# Documentation Templates

This file contains templates for all documentation types. Use these as starting points to ensure consistency.

---

## Domain README.md Template

**Location:** `docs/domains/{domain-name}/README.md`

**When to use:** Creating a new business domain.

```markdown
# {Domain Name}

## Overview
Brief description of what this domain handles (2-3 sentences). Explain the business capability this domain represents.

## Features
- [{Feature 1}](features/feature-1.md) - Brief description
- [{Feature 2}](features/feature-2.md) - Brief description
- [{Feature 3}](features/feature-3.md) - Brief description

## Architecture
High-level overview of how this domain is structured. Key patterns, design decisions, or architectural notes.

### Key Components
- Brief list of main services, models, or systems involved
- How they interact at a high level

## Related Code
- **Backend:** `app/Services/{Domain}/`, `app/Models/{Models}.php`
- **Frontend:** `resources/js/features/{domain}/`
- **Database:** Relevant tables and migrations
- **Tests:** `tests/Feature/{Domain}/`

## API Endpoints
If this domain has API endpoints, link to `api.md` or list key endpoints here:
- `POST /api/{endpoint}` - Description
- `GET /api/{endpoint}` - Description

## Dependencies
- Other domains this domain depends on
- External services or APIs this domain integrates with

## Configuration
Environment variables or configuration files this domain uses (if applicable).
```

---

## Feature Documentation Template

**Location:** `docs/domains/{domain-name}/features/{feature-name}.md`

**When to use:** Documenting a specific user-facing feature or business capability.

```markdown
# {Feature Name}

## What It Does
User-facing explanation of the feature. What problem does it solve? What can users do with it?

## How It Works
System-level explanation. High-level flow of how the feature operates.

### Example Flow
1. User triggers action (e.g., clicks "Login")
2. System validates input
3. System processes request
4. System returns response
5. User sees result

## Key Components

### Backend
- **{ServiceName}** (`app/Services/{Service}.php:line-range`) - What it does
- **{ModelName}** (`app/Models/{Model}.php`) - What it represents
- **{ControllerName}** (`app/Http/Controllers/{Controller}.php:line`) - What endpoints it handles

### Frontend
- **{ComponentName}** (`resources/js/components/{Component}.vue`) - What it displays
- **{ComposableName}** (`resources/js/composables/{composable}.ts`) - What state/logic it provides

### Database
- **{TableName}** - What data it stores
- Relevant indexes or constraints

### API Endpoints
- `POST /api/{endpoint}` - Description, parameters, response
- `GET /api/{endpoint}` - Description, parameters, response

## Implementation Details

### Security Considerations
- Authentication/authorization requirements
- Data validation rules
- Sensitive data handling

### Performance Considerations
- Caching strategies
- Query optimization
- Rate limiting

### Edge Cases
- What happens when X fails?
- How are concurrent requests handled?
- Error handling strategies

### Business Rules
- Validation requirements
- Workflow constraints
- State transitions

## Related Tests
- `tests/Feature/{Feature}Test.php` - What scenarios are covered
- `tests/Unit/{Component}Test.php` - What units are tested

## Code References
- `path/to/file.php:123` - Main entry point
- `path/to/file.php:45-67` - Core business logic
- `path/to/file.vue:89` - UI implementation
- `path/to/test.php:34` - Key test scenario

## Future Improvements
Known limitations or planned enhancements (optional).

## Related Features
- [Other Feature](other-feature.md) - How they relate
- [Another Feature](../../other-domain/features/another.md) - Cross-domain link
```

---

## Layer/Component Documentation Template

**Location:** `docs/layers/{backend|frontend|database}/{type}/{component-name}.md`

**When to use:** Documenting a technical component (service, model, component, etc.).

```markdown
# {Component Name}

## Purpose
What this component/class does and why it exists. One clear paragraph explaining its role in the system.

## Location
`{full/path/to/file.php}` or `{full/path/to/component.vue}`

## Public API

### Methods (for classes)
**`methodName(Type $param): ReturnType`**
- **Purpose:** What the method does
- **Parameters:**
  - `$param` (Type) - Description
- **Returns:** What it returns and in what format
- **Throws:** Any exceptions that might be thrown

**`anotherMethod(): void`**
- **Purpose:** Another method description
- **Parameters:** None
- **Returns:** Nothing

### Props (for Vue components)
- **`propName`** (Type, required) - Description and purpose
- **`optionalProp`** (Type, optional, default: `value`) - Description

### Events (for Vue components)
- **`@event-name`** (payload: Type) - When it's emitted and what data it carries
- **`@another-event`** (payload: Type) - Description

### Slots (for Vue components)
- **`default`** - What goes in the default slot
- **`#named-slot`** - Purpose of the named slot

## Usage Examples

### PHP Example
```php
use App\Services\ServiceName;

$service = new ServiceName();
$result = $service->methodName($param);

// Handle result
if ($result->isSuccessful()) {
    // Do something
}
```

### Vue Example
```vue
<template>
  <ComponentName
    :prop="value"
    :optional-prop="customValue"
    @event-name="handleEvent"
  >
    <template #named-slot>
      Custom content
    </template>
  </ComponentName>
</template>

<script setup>
const handleEvent = (payload) => {
  // Handle event
}
</script>
```

### Laravel Example
```php
// In a controller
public function store(Request $request)
{
    $service = app(ServiceName::class);
    $result = $service->process($request->validated());

    return response()->json($result);
}
```

## Configuration
Environment variables, config files, or options this component uses:

- **`CONFIG_KEY`** - Description and default value
- **`config/file.php`** - Relevant configuration section

## Dependencies

### Internal Dependencies
- `App\Services\OtherService` - Why it's needed
- `App\Models\Model` - What operations are performed

### External Dependencies
- `vendor/package` - What it's used for
- External API - Integration details

## Implementation Notes

### Design Decisions
Explain important architectural choices:
- Why this approach was chosen
- Trade-offs considered
- Alternative approaches considered

### Important Details
- Caching behavior
- Transaction handling
- Event dispatching
- Queue usage

### Gotchas
Things developers should know:
- Common mistakes
- Non-obvious behavior
- Performance considerations

## Related Tests
- `tests/Unit/{Component}Test.php` - What's tested and coverage
- `tests/Feature/{Feature}Test.php` - Integration scenarios

## Related Documentation
- [Feature](../../domains/{domain}/features/{feature}.md) - What feature uses this
- [Other Component]({other-component}.md) - How they work together
```

---

## Quick Template Selection

| Documentation Type | Use Template | Location Pattern |
|--------------------|--------------|------------------|
| New business domain | Domain README | `docs/domains/{domain}/README.md` |
| User-facing feature | Feature | `docs/domains/{domain}/features/{feature}.md` |
| Backend service | Layer/Component | `docs/layers/backend/services/{service}.md` |
| Backend model | Layer/Component | `docs/layers/backend/models/{model}.md` |
| Frontend component | Layer/Component (Vue) | `docs/layers/frontend/components/{component}.md` |
| Frontend composable | Layer/Component | `docs/layers/frontend/composables/{composable}.md` |
| Database table | Layer/Component | `docs/layers/database/schema/{table}.md` |

---

## Template Customization

These templates are starting points. You can:

- **Add sections** relevant to your project
- **Remove sections** that don't apply
- **Adjust examples** to match your stack
- **Extend templates** for specific component types

The key is **consistency** - once you customize, use the same structure across similar docs.

---

## Related Files

- **writing-style.md** - How to write good documentation content
- **file-mapping.md** - Where to place documentation files
- **common-mistakes.md** - Checklist for quality

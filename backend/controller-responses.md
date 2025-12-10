---
paths: app/Http/Controllers/**/*.php
---

# Controller Response Types

This document defines when to use Inertia responses vs JSON responses in controller endpoints.

## Decision Framework

**BEFORE implementing a controller endpoint, determine the response type:**

| Use Case | Response Type | Example |
|----------|--------------|---------|
| Dedicated page with own route | **Inertia** | Settings page, Dashboard, Detail view |
| Reusable dialog/modal | **JSON** | Confirmation dialog, Quick-edit modal |
| API consumed by external clients | **JSON** | Public API, webhook endpoints |

## The Decision Rule

**Ask: "Does this endpoint render a full page?"**
- ✅ YES → Use Inertia response with Vue page component
- ❌ NO → Use JSON response

## Examples

### ❌ WRONG - Using JSON for a dedicated page

```php
public function show(): JsonResponse
{
    return response()->json(SettingsData::from($settings));
}
```

**Problem:** This endpoint serves a dedicated page with its own route (`/settings`), but returns JSON instead of rendering the page via Inertia.

### ✅ RIGHT - Using Inertia for a dedicated page

```php
public function show(): Response
{
    return Inertia::render('Settings/Show', [
        'settings' => SettingsData::from($settings),
    ]);
}
```

**Why:** Dedicated pages should use Inertia to render the full Vue component with proper routing, SEO, and navigation.

### ✅ CORRECT - Using JSON for a modal

```php
public function quickEdit(Request $request): JsonResponse
{
    $data = SettingsData::from($request->validated());
    
    return response()->json([
        'message' => 'Settings updated',
        'settings' => $data,
    ]);
}
```

**Why:** This endpoint is called from a modal/dialog component that's reused across multiple pages. JSON is appropriate here.

## Reasoning

**Inertia responses** are for:
- Pages that have their own dedicated route in `routes/web.php`
- Full-page navigation where the URL changes
- Content that benefits from browser history and bookmarkability

**JSON responses** are for:
- AJAX requests from existing pages
- Modals/dialogs that appear over the current page
- API endpoints consumed by external clients or mobile apps
- Partial page updates without navigation
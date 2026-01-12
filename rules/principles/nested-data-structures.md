# Prefer Nested Structures Over Flat ID Arrays

> **Group related data together in nested objects, don't scatter it across parallel arrays.**

## The Problem

Flat structures with parallel arrays make it impossible to add per-item configuration:

```typescript
// BAD - Flat structure
{
    product_ids: [1, 2, 3],
    quantities: [5, 2, 10],
    prices: [99.99, 149.99, 29.99]
}
```

**Issues:**
- Relies on array index matching (fragile)
- Can't add item-specific properties without another parallel array
- Unclear what belongs to what
- Must restructure everything to extend

## The Solution

Nest related data together:

```typescript
// GOOD - Nested structure
{
    items: [
        { product_id: 1, quantity: 5, price: 99.99 },
        { product_id: 2, quantity: 2, price: 149.99 },
        { product_id: 3, quantity: 10, price: 29.99 }
    ]
}
```

**Benefits:**
- Each item is self-contained
- Easy to add new properties per item (e.g., `discount`, `notes`)
- Relationships are explicit
- Structure mirrors the domain model

## When to Apply

- API request/response payloads
- Data Transfer Objects (DTOs)
- Configuration files
- Service method inputs
- Any structure where items have associated properties

## Key Principle

> If you have an array of IDs and might need per-item data, use an array of objects instead.

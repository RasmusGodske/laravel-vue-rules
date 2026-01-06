---
paths: app/Data/**/*.php
---

# Design for Extensibility: Use Wrapper Objects

**Prefer wrapper objects over direct collections for better extensibility.**

## The Problem

Direct collections in Data classes are hard to extend:

```php
// BAD - Direct collection, hard to extend
class ReportConfigData extends Data
{
    public function __construct(
        /** @var Collection<int, ChartConfigData> */
        #[DataCollectionOf(ChartConfigData::class)]
        public Collection $charts,  // Where do chart-specific settings go?
    ) {}
}
```

**Issues:**
- Can't add properties to a Collection
- Have to pollute parent class with collection-specific details
- Not open for extension

## The Solution

Wrap the collection in a dedicated class:

```php
// GOOD - Wrapper object, open for extension
class ReportConfigData extends Data
{
    public function __construct(
        public ChartsConfigData $charts,
    ) {}
}

class ChartsConfigData extends Data
{
    public function __construct(
        /** @var Collection<int, ChartConfigData> */
        #[DataCollectionOf(ChartConfigData::class)]
        public Collection $items,

        // Easy to add new properties later!
        public ?string $defaultChartType = 'bar',
        public bool $showLegend = true,
        public ?string $colorScheme = null,
    ) {}
}
```

**Benefits:**
- Easy to add collection-specific configuration
- Clear separation of concerns
- Open for extension without modifying parent
- Self-documenting structure

## When to Use Wrapper Objects

Use wrappers for:
- Collections that may need additional configuration
- Groups of related properties that form a cohesive concept
- Any structure that may evolve independently
- When you want to add functionality specific to that collection

## Key Principle

> When designing data structures, ask: "What if we need to add configuration or functionality here later?"
>
> If the answer involves modifying parent classes or awkward workarounds, create a wrapper object.

**It's easier to add properties to an object than to extend a collection.**

# Form Data Classes Pattern

## Overview

This document describes the **Form Data Classes pattern** for creating type-safe, maintainable forms in Laravel applications using Inertia.js and TypeScript.

This pattern provides:
- **Full type safety** from PHP to TypeScript
- **Single form component** that handles both create and edit modes
- **Elimination of duplication** between create and edit logic
- **Rich server context** sent to the frontend in a single request
- **Clean validation** using Spatie Laravel Data

---

## When to Use This Pattern

**NEW code MUST use this pattern** for all forms that:
- Create or edit entities (CRUD operations)
- Use Inertia.js for rendering
- Require server context (dropdowns, options, related data)

**Old code** using traditional Form Requests can be refactored to this pattern over time when it makes sense.

**DO NOT create traditional Laravel FormRequest classes** when using this pattern - Spatie Laravel Data handles validation.

---

## Architecture Overview

The pattern consists of three key components:

```
app/Data/Controllers/{Area}/{Controller}/
└── {Feature}Form/
    ├── {Feature}FormContextData.php     # Server context & form rendering
    ├── {Feature}FormRequestData.php     # Validation & form submission
    └── {Feature}DetailsData.php         # Nested data groups (optional)
```

### Component Responsibilities

1. **FormContextData** - Provides ALL context needed to render the form
   - Available options (categories, tags, authors, etc.)
   - Current mode (`FormMode::CREATE` or `FormMode::UPDATE`)
   - The entity being edited (if any)
   - Default or pre-filled form values

2. **FormRequestData** - Handles form submission and validation
   - Type-safe form field definitions
   - Validation rules via attributes
   - Nested data structures for logical grouping

3. **DetailsData** - Groups related fields logically (optional)
   - Used when FormRequestData would have too many properties
   - Creates nested structure in the form

---

## File Structure

### Directory Naming Convention

```
app/Data/Controllers/{Area}/{Controller}/{Feature}Form/
```

**Examples:**
```
app/Data/Controllers/App/ProductController/ProductForm/
app/Data/Controllers/App/PostController/PostForm/
app/Data/Controllers/Admin/SettingsController/GeneralForm/
```

### Class Naming Conventions

- **Context:** `{Feature}FormContextData`
- **Request:** `{Feature}FormRequestData`
- **Details:** `{Feature}DetailsData` or `{Concept}DetailsData`

**Examples:**
- `ProductFormContextData`
- `ProductFormRequestData`
- `ProductDetailsData`, `PricingDetailsData`

---

## FormMode Enum

Use the `FormMode` enum for type-safe mode handling:

```php
use App\Enums\FormMode;

$mode = FormMode::CREATE;  // or FormMode::UPDATE
```

**Benefits:**
- Type safety (IDE autocomplete)
- No magic strings
- Refactoring-safe

---

## FormContextData Pattern

### Complete Example

```php
<?php

namespace App\Data\Controllers\App\ProductController\ProductForm;

use App\Enums\FormMode;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

#[TypeScript]  // ← Required for TypeScript generation
class ProductFormContextData extends Data
{
    public function __construct(
        public FormMode $mode,                      // Required: FormMode::CREATE or FormMode::UPDATE
        public ?Product $entity,                    // Required: null for create, product for edit
        public ProductFormRequestData $formRequest, // Required: the form data

        // Context data - collections, options, etc.
        public Collection $categories,
        public Collection $tags,
    ) {}

    /**
     * Create form context for creating a new product.
     */
    public static function forCreate(): self
    {
        // 1. Fetch all context data
        $categories = Category::all();
        $tags = Tag::all();

        // 2. Create default form request data
        $formRequest = ProductFormRequestData::forCreate();

        // 3. Return context with CREATE mode
        return new self(
            mode: FormMode::CREATE,
            entity: null,
            formRequest: $formRequest,
            categories: $categories,
            tags: $tags,
        );
    }

    /**
     * Create form context for editing an existing product.
     */
    public static function forEdit(Product $product): self
    {
        // 1. Fetch all context data (same as forCreate)
        $categories = Category::all();
        $tags = Tag::all();

        // 2. Create pre-filled form request data from entity
        $formRequest = ProductFormRequestData::fromEntity($product);

        // 3. Return context with UPDATE mode
        return new self(
            mode: FormMode::UPDATE,
            entity: $product,
            formRequest: $formRequest,
            categories: $categories,
            tags: $tags,
        );
    }
}
```

### Key Principles

1. **Always include `#[TypeScript]` attribute** for automatic TypeScript generation
2. **Required properties:** `mode`, `entity`, `formRequest`
3. **Factory methods:** `forCreate()` and `forEdit()`
4. **Context data:** Include all collections/options needed for dropdowns
5. **No duplication:** Context fetching logic should be DRY (consider extracting to private method)

### DRY Context Fetching (Optional Pattern)

If `forCreate()` and `forEdit()` fetch identical context data, extract it:

```php
/**
 * Fetch common context data used by both create and edit forms.
 */
private static function fetchContextData(): array
{
    return [
        'categories' => Category::orderBy('name')->get(),
        'tags' => Tag::orderBy('name')->get(),
    ];
}

public static function forCreate(): self
{
    $context = self::fetchContextData();

    return new self(
        mode: FormMode::CREATE,
        entity: null,
        formRequest: ProductFormRequestData::forCreate(),
        categories: $context['categories'],
        tags: $context['tags'],
    );
}

public static function forEdit(Product $product): self
{
    $context = self::fetchContextData();

    return new self(
        mode: FormMode::UPDATE,
        entity: $product,
        formRequest: ProductFormRequestData::fromEntity($product),
        categories: $context['categories'],
        tags: $context['tags'],
    );
}
```

---

## FormRequestData Pattern

### Simple Example

```php
<?php

namespace App\Data\Controllers\App\ProductController\ProductForm;

use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use App\Models\Product;

#[TypeScript]  // ← Required for TypeScript generation
class ProductFormRequestData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,

        #[Required]
        public string $description,

        #[Required, Numeric, Min(0)]
        public float $price,

        #[Required]
        public int $category_id,

        public array $tag_ids = [],

        public bool $is_featured = false,
    ) {}

    /**
     * Create form request data with default values for a new product.
     */
    public static function forCreate(): self
    {
        return new self(
            name: '',
            description: '',
            price: 0.00,
            category_id: 0,
            tag_ids: [],
            is_featured: false,
        );
    }

    /**
     * Create form request data from an existing product.
     */
    public static function fromEntity(Product $product): self
    {
        return new self(
            name: $product->name,
            description: $product->description,
            price: $product->price,
            category_id: $product->category_id,
            tag_ids: $product->tags->pluck('id')->toArray(),
            is_featured: $product->is_featured,
        );
    }
}
```

### Complex Example with Nested DetailsData

For forms with many fields, use nested `DetailsData` classes:

```php
<?php

namespace App\Data\Controllers\App\PostController\PostForm;

use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Data;
use App\Models\Post;

#[TypeScript]
class PostFormRequestData extends Data
{
    public function __construct(
        public ContentDetailsData $content,
        public MetadataDetailsData $metadata,
        public PublishingDetailsData $publishing,
    ) {}

    public static function forCreate(): self
    {
        return new self(
            content: ContentDetailsData::forCreate(),
            metadata: MetadataDetailsData::forCreate(),
            publishing: PublishingDetailsData::forCreate(),
        );
    }

    public static function fromEntity(Post $post): self
    {
        return new self(
            content: ContentDetailsData::fromEntity($post),
            metadata: MetadataDetailsData::fromEntity($post),
            publishing: PublishingDetailsData::fromEntity($post),
        );
    }
}
```

```php
<?php

namespace App\Data\Controllers\App\PostController\PostForm;

use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use App\Models\Post;

#[TypeScript]
class ContentDetailsData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $title,

        #[Required]
        public string $body,

        public ?string $excerpt = null,
    ) {}

    public static function forCreate(): self
    {
        return new self(
            title: '',
            body: '',
            excerpt: null,
        );
    }

    public static function fromEntity(Post $post): self
    {
        return new self(
            title: $post->title,
            body: $post->body,
            excerpt: $post->excerpt,
        );
    }
}
```

```php
<?php

namespace App\Data\Controllers\App\PostController\PostForm;

use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use App\Models\Post;

#[TypeScript]
class MetadataDetailsData extends Data
{
    public function __construct(
        #[Max(160)]
        public ?string $meta_description = null,

        public array $tag_ids = [],

        public ?int $author_id = null,
    ) {}

    public static function forCreate(): self
    {
        return new self(
            meta_description: null,
            tag_ids: [],
            author_id: null,
        );
    }

    public static function fromEntity(Post $post): self
    {
        return new self(
            meta_description: $post->meta_description,
            tag_ids: $post->tags->pluck('id')->toArray(),
            author_id: $post->author_id,
        );
    }
}
```

### Key Principles

1. **Always include `#[TypeScript]` attribute**
2. **Always use `#[Validation]` attributes** for explicit validation rules
3. **Factory methods:** `forCreate()` for defaults, `fromEntity()` for editing
4. **Note:** `::from()` is reserved by LaravelData - use `fromEntity()` or specific names
5. **Nested structures:** Use DetailsData classes when you have many related fields (8+ fields)

---

## DetailData Classes

Use DetailData classes to group related fields when FormRequestData would have too many properties.

### When to Use

- Form has more than 8-10 fields
- Fields naturally group by concept (content, metadata, pricing, shipping, etc.)
- Improves readability and organization

### Example

```php
<?php

namespace App\Data\Controllers\App\ProductController\ProductForm;

use Spatie\LaravelData\Attributes\TypeScript;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use App\Models\Product;

#[TypeScript]
class PricingDetailsData extends Data
{
    public function __construct(
        #[Required, Numeric, Min(0)]
        public float $price,

        #[Numeric, Min(0)]
        public ?float $compare_at_price = null,

        #[Numeric, Min(0)]
        public float $cost = 0,

        #[Required]
        public string $currency = 'USD',
    ) {}

    public static function forCreate(): self
    {
        return new self(
            price: 0.00,
            compare_at_price: null,
            cost: 0.00,
            currency: 'USD',
        );
    }

    public static function fromEntity(Product $product): self
    {
        return new self(
            price: $product->price,
            compare_at_price: $product->compare_at_price,
            cost: $product->cost,
            currency: $product->currency,
        );
    }
}
```

**Usage in FormRequestData:**
```php
public function __construct(
    public BasicDetailsData $basic,
    public PricingDetailsData $pricing,
    public InventoryDetailsData $inventory,
) {}
```

---

## Controller Pattern

### Complete Controller Example

```php
<?php

namespace App\Http\Controllers\App;

use App\Data\Controllers\App\ProductController\ProductForm\ProductFormContextData;
use App\Data\Controllers\App\ProductController\ProductForm\ProductFormRequestData;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $formContextData = ProductFormContextData::forCreate();

        return Inertia::render('App/Products/form', [
            'formContextData' => $formContextData,
        ]);
    }

    /**
     * Show the form for editing an existing product.
     */
    public function edit(Product $product)
    {
        // Optional: Store referer for redirect after update
        session()->put('_referer', request()->header('referer'));

        $formContextData = ProductFormContextData::forEdit(
            product: $product,
        );

        return Inertia::render('App/Products/form', [
            'formContextData' => $formContextData,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        // Validate and create type-safe data object
        $formData = ProductFormRequestData::validateAndCreate($request->all());

        // Use service with named arguments for clarity
        $product = app()->make(ProductService::class)->create(
            name: $formData->name,
            description: $formData->description,
            price: $formData->price,
            categoryId: $formData->category_id,
            tagIds: $formData->tag_ids,
            isFeatured: $formData->is_featured,
        );

        return redirect(route('app.products.index'))
            ->with('success', 'Product was created');
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, Product $product)
    {
        // Validate and create type-safe data object
        $formData = ProductFormRequestData::validateAndCreate($request->all());

        // Use service to update
        app()->make(ProductService::class)->update(
            product: $product,
            name: $formData->name,
            description: $formData->description,
            price: $formData->price,
            categoryId: $formData->category_id,
            tagIds: $formData->tag_ids,
            isFeatured: $formData->is_featured,
        );

        // Redirect to stored referer or default route
        $redirectUrl = session()->get('_referer') ?? route('app.products.show', $product->id);
        session()->forget('_referer');

        return redirect($redirectUrl)
            ->with('success', 'Product was updated');
    }
}
```

### Key Points

- **create()**: Calls `forCreate()`, passes to Inertia
- **edit()**: Calls `forEdit()` with entity, passes to Inertia
- **store()/update()**: Use `validateAndCreate()`, then services with named arguments
- **Same form component** for both create and edit
- **Type-safe** throughout - no array access

---

## Frontend Integration (Vue + TypeScript)

### Component Structure

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  formContextData: {
    type: Object as PropType<App.Data.Controllers.App.ProductController.ProductForm.ProductFormContextData>,
    required: true,
  },
});

// Determine mode
const isEditMode = computed(() => props.formContextData.entity !== null);

// Initialize form with context's formRequest (defaults or entity values)
const form = useForm({
  ...props.formContextData.formRequest,
});

const submit = async () => {
  if (isEditMode.value) {
    await form.patch(route('app.products.update', props.formContextData.entity!.id));
  } else {
    await form.post(route('app.products.store'));
  }
};
</script>

<template>
  <form @submit.prevent="submit">
    <h1>{{ isEditMode ? 'Edit Product' : 'Create Product' }}</h1>

    <!-- Basic Fields -->
    <Input
      v-model="form.name"
      label="Product Name"
      :error="form.errors.name"
    />

    <Textarea
      v-model="form.description"
      label="Description"
      :error="form.errors.description"
    />

    <Input
      v-model="form.price"
      type="number"
      step="0.01"
      label="Price"
      :error="form.errors.price"
    />

    <!-- Use context data for dropdowns -->
    <Select
      v-model="form.category_id"
      label="Category"
      :error="form.errors.category_id"
    >
      <option value="">Select a category</option>
      <option
        v-for="category in props.formContextData.categories"
        :key="category.id"
        :value="category.id"
      >
        {{ category.name }}
      </option>
    </Select>

    <!-- Multi-select for tags -->
    <MultiSelect
      v-model="form.tag_ids"
      label="Tags"
      :options="props.formContextData.tags"
      option-label="name"
      option-value="id"
    />

    <!-- Checkbox -->
    <Checkbox
      v-model="form.is_featured"
      label="Featured Product"
    />

    <button type="submit" :disabled="form.processing">
      {{ isEditMode ? 'Update Product' : 'Create Product' }}
    </button>
  </form>
</template>
```

### Accessing Nested Data

For forms with nested `DetailsData`:

```vue
<script setup lang="ts">
const form = useForm({
  ...props.formContextData.formRequest,
});
</script>

<template>
  <!-- Access nested content data -->
  <Input
    v-model="form.content.title"
    label="Title"
    :error="form.errors['content.title']"
  />

  <Textarea
    v-model="form.content.body"
    label="Body"
    :error="form.errors['content.body']"
  />

  <!-- Access nested metadata -->
  <Input
    v-model="form.metadata.meta_description"
    label="Meta Description"
    :error="form.errors['metadata.meta_description']"
  />

  <Select
    v-model="form.metadata.author_id"
    label="Author"
    :error="form.errors['metadata.author_id']"
  >
    <option
      v-for="author in props.formContextData.authors"
      :key="author.id"
      :value="author.id"
    >
      {{ author.name }}
    </option>
  </Select>
</template>
```

### Key Benefits

- **TypeScript types** automatically generated from PHP classes
- **Single component** handles both create and edit
- **Type-safe** access to all properties
- **Context data** available without additional API calls
- **Nested data** keeps structure organized

---

## Validation

### Using Spatie's Validation Attributes

**Always use explicit validation attributes** on FormRequestData and DetailData properties:

```php
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Url;

#[TypeScript]
class ExampleFormRequestData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, Url]
        public string $website,

        #[Required, Numeric, Min(0), Max(100)]
        public float $discount_percentage,

        #[Required, In(['draft', 'published', 'archived'])]
        public string $status,

        public ?string $optional_field = null,  // No Required attribute
    ) {}
}
```

### Common Validation Attributes

```php
// Strings
#[Required]
#[Max(255)]
#[Min(3)]
#[Email]
#[Url]
#[Alpha]
#[AlphaNumeric]

// Numbers
#[Numeric]
#[Integer]
#[Min(0)]
#[Max(100)]
#[Between(0, 100)]

// Dates
#[Date]
#[After('today')]
#[Before('2025-12-31')]

// Arrays
#[Array]
#[ArrayType]

// Validation with specific values
#[In(['option1', 'option2'])]

// Multiple validations
#[Required, Email, Max(255)]
```

### Validation Flow

1. Frontend submits form data
2. Controller receives `Request $request`
3. `validateAndCreate($request->all())` validates against attributes
4. If validation fails → Laravel returns validation errors automatically
5. If validation passes → Returns type-safe Data object

---

## Best Practices

### 1. Always Use Named Arguments

**Good:**
```php
$productService->create(
    name: $formData->name,
    price: $formData->price,
    categoryId: $formData->category_id,
);
```

**Bad:**
```php
$productService->create($formData->name, $formData->price, $formData->category_id);
```

### 2. Import Classes at Top of File

**Good:**
```php
use App\Models\Product;
use App\Models\Category;
use App\Data\Controllers\App\ProductController\ProductForm\ProductFormContextData;

$product = Product::find(1);
```

**Bad:**
```php
$product = \App\Models\Product::find(1);
```

### 3. Single Form Component

Create ONE Vue form component that handles both create and edit modes.

**Good:**
```
resources/js/Pages/App/Products/form.vue  ← Handles both modes
```

**Bad:**
```
resources/js/Pages/App/Products/create.vue
resources/js/Pages/App/Products/edit.vue
```

### 4. Keep Context Fetching DRY

If `forCreate()` and `forEdit()` fetch the same context data, extract to a private method:

```php
private static function fetchContextData(): array
{
    return [
        'categories' => Category::orderBy('name')->get(),
        'tags' => Tag::orderBy('name')->get(),
    ];
}

public static function forCreate(): self
{
    $context = self::fetchContextData();
    // ...
}

public static function forEdit(Product $product): self
{
    $context = self::fetchContextData();
    // ...
}
```

### 5. Use Descriptive Property Names

FormContextData should clearly indicate what the data represents:

**Good:**
```php
public Collection $availableCategories,
public Collection $allTags,
public Collection $activeAuthors,
```

**Bad:**
```php
public Collection $categories,
public Collection $tags,
public Collection $authors,
```

---

## Common Pitfalls

### ❌ Forgetting `#[TypeScript]` Attribute

```php
// Bad - no TypeScript types generated
class ProductFormContextData extends Data
{
    // ...
}

// Good
#[TypeScript]
class ProductFormContextData extends Data
{
    // ...
}
```

### ❌ Not Using Validation Attributes

```php
// Bad - implicit validation only from type hints
public string $name,

// Good - explicit validation rules
#[Required, Max(255)]
public string $name,
```

### ❌ Duplicating Logic Between Create and Edit

```php
// Bad - duplicate code
public static function forCreate(): self
{
    $categories = Category::all();
    $tags = Tag::all();
    // ...
}

public static function forEdit(Product $product): self
{
    $categories = Category::all();  // ← Duplicate
    $tags = Tag::all();  // ← Duplicate
    // ...
}

// Good - DRY
private static function fetchContext(): array
{
    return [
        'categories' => Category::all(),
        'tags' => Tag::all(),
    ];
}
```

### ❌ Creating Traditional FormRequest Classes

```php
// Bad - don't create these anymore
class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => 'required'];
    }
}

// Good - use Data classes
#[TypeScript]
class ProductFormRequestData extends Data
{
    public function __construct(
        #[Required]
        public string $name,
    ) {}
}
```

### ❌ Using `::from()` as Factory Method Name

```php
// Bad - ::from() is reserved by LaravelData
public static function from(Product $product): self
{
    // ...
}

// Good - use fromEntity() or specific name
public static function fromEntity(Product $product): self
{
    // ...
}

public static function fromProduct(Product $product): self
{
    // ...
}
```

### ❌ Not Passing Entity to forEdit()

```php
// Bad - entity not available in context
$formContext = ProductFormContextData::forEdit();

// Good - always pass entity
$formContext = ProductFormContextData::forEdit(product: $product);
```

### ❌ Passing Too Much Unnecessary Context

Only include context data that the form actually needs:

```php
// Bad - includes data not used by the form
public Collection $allUsers,           // Not needed if form doesn't use users
public Collection $allPermissions,     // Not needed
public Collection $systemSettings,     // Not needed

// Good - only what's needed
public Collection $categories,
public Collection $tags,
```

### ❌ Not Using Nested DetailData for Large Forms

```php
// Bad - flat structure with 15+ fields
public function __construct(
    public string $name,
    public string $description,
    public float $price,
    public float $compare_at_price,
    public float $cost,
    public string $sku,
    public int $quantity,
    public bool $track_inventory,
    public string $weight_unit,
    public float $weight,
    // ... 10 more fields
) {}

// Good - nested structure
public function __construct(
    public BasicDetailsData $basic,
    public PricingDetailsData $pricing,
    public InventoryDetailsData $inventory,
    public ShippingDetailsData $shipping,
) {}
```

---

## Summary

This pattern provides a robust, type-safe approach to form handling in Laravel + Inertia.js applications:

✅ **Type safety** from PHP to TypeScript
✅ **Single form component** for create and edit
✅ **No duplication** via factory methods
✅ **Rich context** sent upfront
✅ **Clean validation** with attributes
✅ **Maintainable** and refactoring-safe

**Remember:** NEW code MUST use this pattern. Old code using FormRequest can be migrated over time.

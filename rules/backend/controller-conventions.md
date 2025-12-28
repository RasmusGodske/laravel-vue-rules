---
paths: app/Http/Controllers/**/*.php
---

# Laravel Controller Conventions

Guide for creating clean, maintainable controllers in Laravel with Inertia.js.

## 🚨 Quick Reference - Critical Rules

**Before writing ANY controller code, remember these non-negotiable rules:**

| Rule | ❌ Don't | ✅ Do |
|------|----------|-------|
| **Validation** | `FormRequest` classes | `Data::validateAndCreate()` |
| **Inertia Props** | Raw arrays `['user' => $user]` | `PropsData::from([...])` with `#[TypeScript()]` |
| **JSON Responses** | `response()->json([...])` | `ResponseData::from([...])` with `#[TypeScript()]` |
| **Business Logic** | In controllers | In services/models |

**The #1 mistake:** Using Laravel's `FormRequest` for validation instead of Spatie Data classes.

---

## Controller Structure

Controllers should be thin and focused on HTTP concerns:
- Receive requests
- Validate input (using Data classes)
- Coordinate business logic (delegate to services/models)
- Return responses (Inertia renders or redirects)

**Don't:** Put business logic in controllers
**Do:** Keep controllers focused on HTTP layer

## Validation: Use Data Classes, Not FormRequests

**IMPORTANT:** Prefer Data classes over FormRequest classes for validation.

### Why Data Classes Over FormRequests?

1. **Single Source of Truth** - Data class defines both structure AND validation
2. **Type Safety** - Get typed objects, not arrays
3. **Frontend Integration** - Automatically generates TypeScript types
4. **Cleaner API** - `DataClass::validateAndCreate()` vs manually calling `$request->validated()`
5. **Reusability** - Same Data class can be used in controllers, services, jobs, etc.

### ❌ BAD - Using FormRequest

```php
// app/Http/Requests/StoreDogInvitationRequest.php
class StoreDogInvitationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'dog_id' => ['required', 'exists:dogs,id'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}

// Controller
class DogInvitationController extends Controller
{
    public function store(StoreDogInvitationRequest $request)
    {
        $validated = $request->validated(); // Returns array

        // Now you have a loosely-typed array to work with
        DogInvitation::create($validated);

        return redirect()->back();
    }
}
```

**Problems:**
- Returns untyped array from `$request->validated()`
- No TypeScript types for frontend
- Validation rules separate from data structure
- Can't reuse in other contexts (jobs, services, etc.)

### ✅ GOOD - Using Data Classes

```php
// app/Data/DogInvitation/StoreDogInvitationData.php
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;

class StoreDogInvitationData extends Data
{
    public function __construct(
        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[Exists('dogs', 'id')]
        public int $dogId,

        #[Max(500)]
        public ?string $message = null,
    ) {}
}

// Controller
class DogInvitationController extends Controller
{
    public function store(Request $request)
    {
        // Validates AND returns typed object in one line
        $data = StoreDogInvitationData::validateAndCreate($request->all());

        // $data is now a typed StoreDogInvitationData object
        DogInvitation::create([
            'email' => $data->email,
            'dog_id' => $data->dogId,
            'message' => $data->message,
        ]);

        return redirect()->back();
    }
}
```

**Benefits:**
- Type-safe `$data` object
- Validation and structure in one place
- Can add `#[TypeScript()]` for frontend types
- Reusable in services, jobs, etc.
- Clean, readable validation rules

## Inertia Props: Always Use Data Classes

**IMPORTANT:** When returning props to Vue components, ALWAYS use Data classes for type safety.

### ❌ BAD - Raw Arrays

```php
class DogController extends Controller
{
    public function show(Dog $dog)
    {
        return Inertia::render('Dogs/Show', [
            'dog' => [
                'id' => $dog->id,
                'name' => $dog->name,
                'breed' => $dog->breed,
                'birth_date' => $dog->birth_date,
                'weight' => $dog->weight,
                'photo' => $dog->photo,
                // ... 10 more properties
            ],
            'stats' => [
                'totalActivities' => 42,
                'averageWeight' => 25.5,
                'lastActivity' => '2025-01-15',
            ],
            'lastMood' => $lastMood,
            'dailyFoodStats' => $dailyFoodStats,
        ]);
    }
}
```

**Problems:**
- No TypeScript types in frontend
- Manually update frontend `defineProps` when adding properties
- Easy to make typos in property names
- No IDE autocomplete in Vue components
- Changes break silently

### ✅ GOOD - Dedicated Props Data Class

```php
// app/Data/Http/Controllers/DogController/ShowPropsData.php
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Props for Dogs/Show page
 *
 * @property Dog $dog The dog being displayed
 * @property Collection<int, Dog> $allDogs All user's dogs for sidebar
 */
#[TypeScript()]
class ShowPropsData extends Data
{
    public function __construct(
        public Dog $dog,

        #[DataCollectionOf(Dog::class)]
        public Collection $allDogs,

        public DogStatsData $stats,
        public ?MoodData $lastMood,
        public DailyStatsData $dailyFoodStats,
        public DailyStatsData $dailyDrinkStats,
        public DailyStatsData $dailyPeeStats,
        public DailyStatsData $dailyPoopStats,
    ) {}
}

// Controller
class DogController extends Controller
{
    public function show(Dog $dog)
    {
        return Inertia::render('Dogs/Show', ShowPropsData::from([
            'dog' => $dog,
            'allDogs' => $allDogs,
            'stats' => $stats,
            'lastMood' => $lastMood,
            'dailyFoodStats' => $dailyFoodStats,
            'dailyDrinkStats' => $dailyDrinkStats,
            'dailyPeeStats' => $dailyPeeStats,
            'dailyPoopStats' => $dailyPoopStats,
        ]));
    }
}
```

**Benefits:**
- Frontend automatically gets TypeScript types
- Add/remove props without touching frontend
- IDE autocomplete in Vue components
- Type errors caught at compile time
- Self-documenting controller responses

### Frontend Usage (Automatic)

After running `composer dev-setup`, Vue components automatically get types:

```vue
<script setup lang="ts">
import type { ShowPropsData } from '@/types/generated'

// Fully typed props!
const props = defineProps<ShowPropsData>()

// IDE autocomplete works:
console.log(props.dog.name)
console.log(props.stats.totalActivities)
</script>
```

### Naming Convention for Props Data Classes

```
App/Data/Http/Controllers/{ControllerName}/{MethodName}PropsData.php
```

Examples:
- `App/Data/Http/Controllers/DogController/ShowPropsData.php`
- `App/Data/Http/Controllers/DogController/IndexPropsData.php`
- `App/Data/Http/Controllers/DogController/EditPropsData.php`
- `App/Data/Http/Controllers/OrderController/ShowPropsData.php`

**Important:** Always add `#[TypeScript()]` attribute to Props Data classes!

## Resource Controllers

Use Laravel's resource controller pattern:

```bash
php artisan make:controller OrderController --resource
```

Standard methods:
- `index()` - List all resources
- `create()` - Show create form (if not using Inertia modal)
- `store()` - Create new resource
- `show()` - Display single resource
- `edit()` - Show edit form (if not using Inertia modal)
- `update()` - Update existing resource
- `destroy()` - Delete resource

**With Inertia:** Often skip `create()` and `edit()` methods - render forms in `index()` or `show()` instead.

## Return Types

Always specify return types:

```php
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

public function index(): InertiaResponse
{
    return Inertia::render('Orders/Index', ...);
}

public function store(Request $request): RedirectResponse
{
    // ...
    return redirect()->route('orders.show', $order);
}

public function apiIndex(): JsonResponse
{
    return response()->json(['orders' => $orders]);
}
```

## Authorization

Use policies for authorization:

```php
// In controller
public function update(Request $request, Order $order): RedirectResponse
{
    // Method 1: authorize() helper
    $this->authorize('update', $order);

    // Method 2: Gate facade
    if (Gate::denies('update', $order)) {
        abort(403);
    }

    // Method 3: In route middleware
    // Route::put('orders/{order}', [OrderController::class, 'update'])
    //     ->middleware('can:update,order');

    // ...
}
```

**Prefer:** `$this->authorize()` for clear inline authorization checks.

## Query Optimization

Always eager load relationships to avoid N+1 queries:

```php
public function index(): InertiaResponse
{
    $orders = Order::query()
        ->with(['customer', 'items.product']) // Eager load
        ->latest()
        ->paginate(15);

    return Inertia::render('Orders/Index', IndexPropsData::from([
        'orders' => $orders,
    ]));
}
```

Use `withCount()` for counting relationships:

```php
$orders = Order::withCount('items')->get();
// Now each order has $order->items_count
```

## JSON API Responses

**CRITICAL:** Always use Data classes for JSON API responses, just like Inertia props.

### ❌ WRONG - Raw Arrays
```php
public function getCalculatables(Commission $commission, int $dataSourceId): JsonResponse
{
    $calculatables = $commission->calculatables()->get();

    return response()->json([
        'calculatables' => $calculatables->map(fn($c) => [
            'id' => $c->id,
            'title' => $c->title,
        ]),
    ]);
}
```

**Problems:**
- No TypeScript types for frontend
- No IDE autocomplete
- Easy to make typos in property names
- Can't enforce structure across endpoints

### ✅ CORRECT - Data Classes
```php
// App/Data/Controllers/App/CommissionController/GetCalculatablesResponseData.php
#[TypeScript()]
class GetCalculatablesResponseData extends Data
{
    public function __construct(
        #[DataCollectionOf(CalculatableItemData::class)]
        public Collection $calculatables,
    ) {}
}

#[TypeScript()]
class CalculatableItemData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
    ) {}
}

// Controller
public function getCalculatables(Commission $commission, int $dataSourceId): JsonResponse
{
    $calculatables = $commission->calculatables()->get();

    return response()->json(
        GetCalculatablesResponseData::from([
            'calculatables' => $calculatables,
        ])
    );
}
```

**Benefits:**
- Frontend automatically gets types
- Type-safe API contract
- Refactoring-safe
- Self-documenting

### Naming Convention for API Response Data Classes

```
App/Data/Controllers/{Area}/{Controller}/{MethodName}ResponseData.php
```

Examples:
- `App/Data/Controllers/App/CommissionController/GetCalculatablesResponseData.php`
- `App/Data/Controllers/App/OrderController/GetOrderDetailsResponseData.php`
- `App/Data/Controllers/Api/V1/UserController/CreateUserResponseData.php`

## Checklist for Controllers

Before considering a controller complete:

- ✅ Returns typed objects (Data classes), not raw arrays
- ✅ Uses Data classes for validation (not FormRequest, unless authorization needed)
- ✅ Uses Props Data classes for all Inertia renders with `#[TypeScript()]`
- ✅ Uses Response Data classes for all JSON API responses with `#[TypeScript()]`
- ✅ Props Data classes follow naming convention: `App/Data/Http/Controllers/{ControllerName}/{MethodName}PropsData.php`
- ✅ API Response Data classes follow naming convention: `App/Data/Controllers/{Area}/{Controller}/{MethodName}ResponseData.php`
- ✅ Specifies return types for all methods
- ✅ Eager loads relationships to avoid N+1 queries
- ✅ Returns flash messages for user feedback
- ✅ Keeps business logic in services/models, not controllers
- ✅ Uses named routes for redirects
- ✅ Uses route model binding for resource parameters

---

## Common Mistakes (From Real Agent Reviews)

These mistakes were caught in code review and blocked from merging:

### ❌ Mistake 1: Creating FormRequest Instead of Data Class

```php
// BLOCKED - Agent created a FormRequest class
// app/Http/Requests/StoreServerRequest.php
class StoreServerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'discord_channel_id' => ['required', 'string', 'unique:servers'],
        ];
    }
}
```

**Fix:** Create a Data class instead:

```php
// app/Data/Server/StoreServerData.php
#[TypeScript()]
class StoreServerData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,

        #[Required]
        #[Unique('servers', 'discord_channel_id')]
        public string $discord_channel_id,
    ) {}
}
```

### ❌ Mistake 2: Using request() in Controller Instead of Injected Request

```php
// BLOCKED - Using global helper
public function store()
{
    $name = request()->input('name');  // ❌
    $user = request()->user();         // ❌
}
```

**Fix:** Use injected Request:

```php
public function store(Request $request)
{
    $data = StoreServerData::validateAndCreate($request->all());  // ✅
    $user = $request->user();  // ✅
}
```

### ❌ Mistake 3: Returning Raw Arrays from Inertia

```php
// BLOCKED - No TypeScript types for frontend
return Inertia::render('Servers/Index', [
    'servers' => $servers,
    'canCreate' => $user->can('create', Server::class),
]);
```

**Fix:** Use a Props Data class:

```php
return Inertia::render('Servers/Index', IndexPropsData::from([
    'servers' => $servers,
    'canCreate' => $user->can('create', Server::class),
]));
```

### Detection Checklist

Before submitting controller code, search for:

- [ ] `extends FormRequest` - Should be Data class instead
- [ ] `request()->` - Should use injected `$request`
- [ ] `Inertia::render('...', [` without `PropsData::from` - Should use Props Data class
- [ ] `response()->json([` without `ResponseData::from` - Should use Response Data class
- [ ] Business logic (queries, calculations) in controller - Should be in service/model

---

## Common Imports

```php
// Controllers
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

// Models
use App\Models\Dog;
use App\Models\Order;

// Data classes
use App\Data\Http\Controllers\DogController\ShowPropsData;
use App\Data\DogInvitation\StoreDogInvitationData;

// Services
use App\Services\DogStatisticsService;
```

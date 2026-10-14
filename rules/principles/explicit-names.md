---
paths:
  - "app/**/*.php"
  - "resources/js/**/*.{vue,ts,tsx}"
  - "tests/**/*.php"
  - "e2e/**/*.ts"
---

# Explicit Names

> **One line, read on its own, says what happens. A variable or property is named after the type it is declared as, a method starts with its action, and length is never a reason to shorten a name.**

The why: the [explicit-names](https://github.com/RasmusGodske/design-principles/tree/main/skills/design-principles/explicit-names) design principle. Every name that hides what it stands for sends the reader (a person, or an agent with limited context) to a constructor, a declaration or another file. An explicit name lets them understand the line without reading anything else.

## Variables and properties: the declared type, in camelCase

```php
// ✅ Readable without the constructor
public function __construct(
    private readonly OrderLineOnBehalfService $orderLineOnBehalfService,
    private readonly OrderOverviewDataViewService $orderOverviewDataViewService,
) {}

$this->orderLineOnBehalfService->listOrderLines($actor, $order, $orderLinesActionPayloadData);

// ❌ Reads like a collection; the reader has to look up what it is
public function __construct(
    private readonly OrderLineOnBehalfService $lines,
    private readonly OrderOverviewDataViewService $view,
) {}

$this->lines->for($actor, $order, $payload);
```

- **The whole class name**, suffix included (`Service`, `Data`, `QueryService`). A plural name (`$pendingOrders`) is only for a collection, named after what it holds.
- **Two of the same type: the role goes in front**: `$approvingUser`, `$submittingUser`; never `$user2`, and never the role alone (`$approver`).
- **An interface type: name after the interface**, not a concrete kind it may hold: `Principal $approvingPrincipal`.
- **No abbreviations**: not `$cu`, `$tx`, `$cfg`, `$svc`, `$repo`. `$id` and `$url` are fine.
- **A tiny scope may be short**: a loop variable or a one-line closure's parameter whose whole life is on screen (`foreach ($pendingOrders as $order)`, `fn (Order $order) => …`).

## Methods: action first, then what it acts on

```php
// ✅
getOrderSummary()      // returns one thing
listOrderLines()       // returns several
buildOrderOverviewView()  // assembles a value without storing it
createOrder() / updateOrderAddress() / removeOrderLine()   // change stored data
mayCancelOrder() / isArchived() / hasOpenInvoices()        // true or false

// ❌ A bare noun hides whether it reads, builds, changes or deletes
summary(), lines(), for(), page(), devices(), openOf()
```

Use the same verb for the same kind of action everywhere, so the verb carries meaning. Laravel's own conventions keep their names: controller actions (`index`, `show`, `store`, `update`, `destroy`), Eloquent relations (`$order->lines()`), and framework hooks (`handle`, `boot`, `rules`).

## Vue and TypeScript

The same rules apply: `const pendingOrders = …`, not `const items`; a composable's returned functions start with their action (`removeOrderLine()`, not `remove()`); a component's emitted event names what happened (`order-line-removed`).

## Scope

New code and code you touch. Renaming untouched code is its own change, not part of a feature.

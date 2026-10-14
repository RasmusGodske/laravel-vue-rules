---
paths:
  - "app/**/*.php"
---

# On-Behalf Services, Data Views, Payloads and Views

> **Everything an actor asks for goes through an on-behalf service; everything a page, API endpoint or MCP tool shows is built by one data view. Controllers build the actor, call one of them, and present the result.**

The why: the [acting-on-behalf](https://github.com/RasmusGodske/design-principles/tree/main/skills/design-principles/acting-on-behalf) and [data-view](https://github.com/RasmusGodske/design-principles/tree/main/skills/design-principles/data-view) design principles. This rule is the Laravel shape.

## Names and places (in the module that owns the entity)

| Thing | Name | Lives in |
|---|---|---|
| On-behalf service (checks the actor, delegates) | `{Entity}{Thing}OnBehalfService` | `Public/Services/` |
| Base service (the record's rules, knows no actor) | `{Entity}{Thing}Service` / `…QueryService` | `Services/` or `Public/Services/` |
| Data view (composes on-behalf answers into one view) | `{Entity}{View}DataViewService` | `Public/Services/` |
| A data view's result | `{Entity}{View}ViewData` | `Public/Data/Views/` |
| An action's input | `{Entity}{Action}ActionPayloadData` | `Public/Data/Payloads/` |

`{View}` names what the view *is* (`Overview`, `List`, `Edit`), never where it is shown. A view is never a reason to create a module or sub-module.

## Shape

```php
// ✅ Controller: one line, decides nothing
public function overview(Order $order, OrderOverviewDataViewService $view): Response
{
    return Inertia::render('Orders/Pages/OrderOverviewInertiaPage', $view->for($this->actor(), $order));
}

// ✅ On-behalf service: subject + payload as separate arguments; limits passed down explicitly
public function lines(Actor $actor, Order $order, OrderLinesActionPayloadData $payload): OrderLinesData
{
    $principal = $actor->requirePrincipal(User::class);   // states the supported principal kind
    $this->ensureMayView($principal, $order);              // not permitted to open → not found (404)

    return $this->orders->lines($order, $payload, includeCost: $principal->isAdmin());
}
```

- **Actor, never the request.** On-behalf services take an `Actor` (on behalf of a principal, performed by a performer). No `auth()`, `request()` or context facade inside a service.
- **`include<Thing>` arguments have no default value**, so a new restricted part forces every on-behalf service to decide it.
- **Hidden means absent:** a restricted part is typed `X|Optional`; `null` means genuinely empty.
- **Actions** carry their state: `ActionData|Optional` per action (absent = not permitted; blocked = `available: false` with the reason from the same base-service function the write path refuses with).
- **Data views never look at the actor**, embed on-behalf answers unchanged, and never call another data view.
- **The payload is passed whole and unchanged**; it never carries the subject's id.

## Red flags

- `can()` / `hasRole()` / `isAdministrator()` in a controller, data view or Vue page to decide visibility
- A controller with more than one service call, or reshaping input before passing it on
- A base service taking the actor, or an on-behalf service running its own queries
- `…PropsData` for a new page (it's `…ViewData` from a data view)

---
paths:
  - "tests/**/*.php"
---

# Writing Tests in Pest

> **PHP tests are Pest functions, written so PHPStan checks every call in them; a PHPUnit class test converted to Pest keeps every test and assertion.**

Pest's test impact analysis (`--tia`) only works on Pest tests, and runs just the tests a change touches. These rules keep tests checkable by PHPStan (with `pestphp/pest-plugin-phpstan`), so a renamed or removed method still fails static analysis in seconds. This file says how a test file is written; what a good test checks is in `testing-conventions.md`. The `was:` comments map PHPUnit's class syntax onto Pest's.

## The shape of a file

```php
<?php

namespace Tests\Feature\Orders;                       // keep the file's namespace

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);        // was: extends TestCase + use RefreshDatabase
covers(OrderService::class);                          // was: #[CoversClass(OrderService::class)]

beforeEach(function () {                              // was: setUp()
    $this->actingAs(User::factory()->create());
});

test('an order total includes tax', function () {     // was: test_an_order_total_includes_tax()
    $order = Order::factory()->withItems(subtotal: 100)->create();

    $this->assertSame(125, app()->make(OrderService::class)->totalWithTax($order));
});
```

- **Base class per file with `uses()`**: the class the test extended, plus its traits. Not through `tests/Pest.php` when directories mix base classes. PHPStan reads `uses()`, so `$this->` resolves to that class.
- **`covers()` / `coversNothing()`** on every file, as the attributes were (Pest has no `coversNothing()`; define one in `tests/Pest.php`).
- **Test name** = the old method name as a sentence; a docblock that explained *why* becomes a `//` comment above it.
- **`afterEach()`** for `tearDown()`; **`dataset()`** or `->with([...])` for a data provider, keeping the cases' names.
- **Keep the assertions as they are.** A conversion changes structure, not what is asserted; rewriting to `expect()` is a separate change.

## No state of your own on `$this`

Pest's PHPStan plugin types what `beforeEach()` assigns, but accepts any property name on a test case, so a typo or a removed property is never reported, and a helper function cannot see the state at all.

```php
// ✅ a typed helper, or a local variable in the test
function orderService(): OrderService
{
    return app()->make(OrderService::class);
}
test('…', fn () => orderService()->totalWithTax($order));

// ❌ state PHPStan cannot check
beforeEach(fn () => $this->service = app()->make(OrderService::class));
test('…', fn () => $this->service->totalWithTax($order));
```

State the **base class declares** (typed properties set by its own setup methods) is fine. Enforce it with a PHPStan rule that reports any other `$this->x` in a Pest test or hook.

When several tests share fixtures the old `setUp()` built, keep them in a **state class** in the file, built by a factory each test calls first:

```php
final class OrderTotalsTestState
{
    public Customer $customer;

    public OrderService $service;

    public static function create(TestCase $test): self   // was: setUp()
    {
        $state = new self;
        $state->customer = $test->getCustomer();
        $state->service = app()->make(OrderService::class);

        return $state;
    }
}

test('an order total includes tax', function () {
    $state = OrderTotalsTestState::create($this);

    $this->assertSame(125, $state->service->totalWithTax(orderFor($state, subtotal: 100)));
});
```

A `setUp()` that keeps no state becomes `beforeEach()`; the class's constants and static properties move to the state class too.

## Helper functions

A private method of the old class becomes a typed function in the file.

- **It has no `$this`.** Assert with `PHPUnit\Framework\Assert::assertSame(...)`; when it needs the test case, take it as a typed parameter (`function orderWithItems(TestCase $test): Order`) and pass `$this`; when it needs the state, take the state class (`OrderTotalsTestState $state`). Protected test-case methods can only be called inside the test closures, which is why the base classes' helpers are public.
- **Its name must be unique in the directory.** Functions take the file's namespace, so two files in one directory declaring `makeOrder()` crash the whole run ("Cannot redeclare"). Name it after what the file tests.
- **Never name it like a framework global helper** (`response`, `route`, `config`, `app`, `collect`, `now`): `httpResponse()`, not `response()`.
- **A helper two files need** goes into a shared class of static methods under `tests/` (like the fake API payload builders), or a base class's public method, not into both files.

## Time, randomness and the network are fixed

The base test cases make every test deterministic (the [`deterministic-tests`](https://github.com/RasmusGodske/design-principles) design principle): the clock starts at the same moment every run, Faker and `mt_rand` are seeded from the test's name, and an HTTP request no fake answers throws.

- **A test about a moment states it**: `$this->travelTo('2026-01-31 23:59')`, not a date computed from "now" that happens to work today.
- **A test about a remote answer fakes it** with the provider's fake server or `Http::fake()`. Never let a request reach the network; `Http::allowStrayRequests()` is only for a test whose subject is the network itself.
- **Assert on what you built**: set every value the assertion depends on. Fake data fills only what the test doesn't care about; never loop assertions over rows whose number came from random data.

## Verifying a conversion

1. Same tests, same assertions: the file's run reports the same `N passed (M assertions)` before and after. Pest counts Mockery expectations as assertions, so a class test that closed Mockery in its own `tearDown()` shows more afterwards.
2. Static analysis passes without adding baseline entries.
3. The code formatter (`pint`) passes on the file.

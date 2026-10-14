---
paths:
  - "tests/**/*.php"
---

# Writing and Converting Tests in Pest

> **New PHP tests are Pest functions; a PHPUnit class test converted to Pest keeps every test and assertion, and stays fully visible to PHPStan.**

Pest's test impact analysis (`--tia`) only works on Pest tests, and runs just the tests a change touches. These rules keep converted tests checkable by PHPStan (with `pestphp/pest-plugin-phpstan`), so a renamed or removed method still fails static analysis in seconds. Where they disagree with `testing-conventions.md` (`test_` method names, `#[CoversClass]`, `setUp()`), this file wins for Pest tests; everything else there still applies.

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
- **`covers()` / `coversNothing()`** on every file, as the attributes were.
- **Test name** = the old method name as a sentence; a docblock that explained *why* becomes a `//` comment above it.
- **`afterEach()`** for `tearDown()`; **`dataset()`** or `->with([...])` for a data provider, keeping the cases' names.
- **Keep the assertions as they are.** A conversion changes structure, not what is asserted; rewriting to `expect()` is a separate change.

## No state of your own on `$this`

Pest's PHPStan plugin allows any property on a test case, so PHPStan cannot see a typo or a removed property you assign yourself.

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

State the **base class declares** (typed properties set by its own setup methods) is fine.

## Helper functions

A private method of the old class becomes a typed function in the file.

- **It has no `$this`.** Assert with `PHPUnit\Framework\Assert::assertSame(...)`; when it needs the test case, take it as a typed parameter (`function orderWithItems(TestCase $test): Order`) and pass `$this`. Protected test-case methods can only be called inside the test closures.
- **Its name must be unique in the directory.** Functions take the file's namespace, so two files in one directory declaring `makeOrder()` crash the whole run ("Cannot redeclare"). Name it after what the file tests.
- **Never name it like a framework global helper** (`response`, `route`, `config`, `app`, `collect`, `now`): `httpResponse()`, not `response()`.
- **A helper two files need** goes into a shared class or trait under `tests/`, not into both files.

## Verifying a conversion

1. Same tests, same assertions: the file's run reports the same `N passed (M assertions)` before and after.
2. Static analysis passes without adding baseline entries.
3. The code formatter (`pint`) passes on the file.

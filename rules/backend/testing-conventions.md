---
paths: tests/**/*.php
---

# PHP Testing Conventions

> **Every test catches a bug someone could really write: it builds exactly the data it is about with factories, does one thing, and asserts the outcome specifically.**

This file says what a good test checks. How a Pest test file is written (`uses()`, `covers()`, helpers, no state on `$this`) is in `pest-conventions.md`. Why every test gives the same answer on every run is the `deterministic-tests` design principle ([design-principles](https://github.com/RasmusGodske/design-principles)).

## A test must catch a real bug

Before writing a test, answer: **which bug or regression does this catch?** No clear answer, no test.

```php
// ❌ Catches nothing: restates the code or what PHP already guarantees
test('the status enum has 13 cases', function () {
    $this->assertCount(13, Status::cases());
});

test('the address data keeps its constructor arguments', function () {
    $address = new AddressData(street: '1 Main St', city: 'Springfield');

    $this->assertSame('1 Main St', $address->street);
});

// ✅ Fails when the calculation breaks
test('an order total includes 25% tax', function () {
    $order = Order::factory()->withItems(subtotal: 100)->create();

    $this->assertSame(125, $order->totalWithTax());
});
```

Test behaviour that can go wrong: calculations, mappings, rules, permissions, round-trips through non-trivial casts. **A test that breaks when the code is legitimately changed, but passes when a bug is introduced, is worse than no test.**

## One behaviour, named as a sentence

- **The name says the behaviour and its outcome**, readable on its own in a failure list: `'a user cannot delete a published post'`, not `'delete'` or `'it works'`.
- **One behaviour per test.** Two outcomes worth naming are two tests.
- **Arrange, act, assert**, separated by a blank line. A comment explains *why* where the setup is not obvious; the name already says *what*.
- **Mirror `app/`**: `app/Services/Payments/PaymentService.php` → `tests/Feature/Services/Payments/PaymentServiceTest.php`.
- **Several inputs for one rule** use `->with([...])`, with a name per case:

```php
test('registration rejects an invalid email', function (string $email) {
    $this->postJson('/register', ['email' => $email, 'password' => 'secret123'])
        ->assertJsonValidationErrors(['email']);
})->with([
    'missing @' => ['user.example.com'],
    'missing domain' => ['user@'],
    'empty' => [''],
]);
```

## Build data with factories

```php
// ✅ Factories, related through for(), has() and recycle()
$customer = Customer::factory()->create();
$order = Order::factory()
    ->for($customer)
    ->has(OrderItem::factory()->count(2))
    ->create(['status' => OrderStatus::PENDING]);

// ❌ Direct creation and hard-coded ids
$order = Order::create(['customer_id' => 1, 'status' => 'pending']);
```

- **Set only what the test is about**; the factory fills the rest. Whatever the assertion depends on, the test sets explicitly, never a generated value.
- **`recycle($model)`** when nested factories must share a parent (every row under the same tenant). Without it each nested factory creates its own.
- **A factory needs `@extends Factory<Order>`** so `create()` returns `Order`, not `Model`. Fix a missing one in the factory, never with `instanceof` checks in the tests.
- **After saving a related model, `->refresh()` the parent**: Eloquent keeps the relation it loaded before.

### Traps

- **A model that must be written through a service** throws when its factory's plain `create()` is used ("created outside of OrderService"). Use the factory's service method (`createOneViaService()`); never disable the guard.
- **`actingAs()` with a model that isn't the authenticatable user** (a membership or pivot row) authenticates nothing, or the wrong thing, and tenant-scoped queries then return nothing. Use the project's acting-as helper.

The concrete models and helper names are listed in the project's own rules (`.claude/rules/project/`).

## Assert specifically

```php
// ✅ The failure shows what was expected and what came instead
$this->assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
$this->assertCount(3, $orders);
$this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);

// ❌ Fails with "false is not true"
$this->assertTrue($order->fresh()->status === OrderStatus::SHIPPED);
$this->assertTrue(count($orders) === 3);
```

- **`assertSame` over `assertEquals`** unless loose comparison is the point (comparing models, floats with a delta).
- **A message when the values alone won't say what broke**: `$this->assertSame(200, $total, 'The total should be price × quantity over all items')`.
- **Use the suite's assertion style.** Don't mix `expect()` into a file that uses `$this->assert…()`.
- **Never `assertTrue(true)`** or another assertion that cannot fail. Assert the state the code produced, or delete the test.
- **Don't `assertNotNull()` a non-nullable value**: PHPStan reports it, and it checks nothing. Assert the value.
- **Exceptions**: `$this->expectException(PaymentDeclined::class)` before the call that throws.

## Fake the outside world, not your own code

```php
// ✅ Laravel's fakes for side effects, asserted afterwards
Mail::fake();
Queue::fake();

$order->complete();

Mail::assertSent(OrderConfirmation::class, fn ($mail) => $mail->order->is($order));
Queue::assertPushed(ShipOrder::class, 1);

// ✅ A mock for a service you don't own
$this->mock(PaymentGateway::class)
    ->shouldReceive('charge')->once()->with(100, $customer)->andReturn(ChargeResult::success());

// ❌ Mocking your own models or the code under test
$order = $this->mock(Order::class);
```

`Event::fake()`, `Bus::fake()`, `Notification::fake()` and `Storage::fake()` work the same way. HTTP calls are answered with `Http::fake()` or the provider's fake server; a request nothing answers throws.

## Validation failures: web routes vs JSON

A `ValidationException` becomes a **302 redirect with session errors** for `->post()` and a **422 JSON response** for `->postJson()`.

```php
// ✅ JSON: 422
$this->postJson('/orders', ['quantity' => -1])
    ->assertStatus(422)
    ->assertJsonValidationErrors(['quantity']);

// ✅ Web form: redirect with errors in the session
$this->post('/orders', ['quantity' => -1])
    ->assertRedirect()
    ->assertSessionHasErrors(['quantity']);

// ❌ A web POST never returns 422
$this->post('/orders', ['quantity' => -1])->assertStatus(422);
```

## Running tests

Use the project's test command (see its `AGENTS.md` or `CLAUDE.md`), filtered to the file you are working on while you work; the full gate runs before you push. Don't run the bare test runner when the project wraps it: the wrapper sets up what parallel and browser runs need.

## Checklist

1. It catches a named bug, and fails when you break the code it tests.
2. The name is a sentence about behaviour and outcome.
3. Data comes from factories; everything the assertion depends on is set in the test.
4. Assertions are specific, and every one of them can fail.
5. Only what you don't own is faked or mocked.
6. The file follows `pest-conventions.md`: base class through `uses()`, `covers()`, no state of its own on `$this`.

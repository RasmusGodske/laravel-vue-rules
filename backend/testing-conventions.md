# PHP Testing Conventions

This document provides comprehensive PHP/Laravel testing conventions. Follow these patterns to create maintainable, readable tests.

## Core Testing Principles

1. **Independence**: Tests should be independent and isolated
2. **Single Purpose**: Each test verifies a single feature or behavior
3. **No Dependencies**: Avoid test interdependencies
4. **Fast Execution**: Tests should run quickly
5. **Deterministic**: Same result every time

## Test Structure & Naming

### Test Method Naming

**Formula**: `test_{what}__{conditions}__{expected_outcome}`

```php
// ✅ Excellent examples:
public function test_order_total_calculates_correctly_with_discount()
public function test_user_cannot_delete_published_post()
public function test_email_sends_after_successful_payment()
public function test_password_reset_token_expires_after_one_hour()

// ❌ Avoid:
public function test_order()  // Too vague
public function testCalculation()  // Not descriptive enough
```

**Always add PHPDoc:**
```php
/**
 * Test that order total includes tax and shipping
 */
public function test_order_total_includes_tax_and_shipping()
{
    // Test implementation
}
```

### File Structure

**Mirror the app/ directory structure:**
```
app/Services/Payment/PaymentService.php
→ tests/Feature/Services/Payment/PaymentServiceTest.php

app/Models/Order.php
→ tests/Unit/Models/OrderTest.php
```

### Test Structure: Arrange-Act-Assert

Use the AAA pattern:

```php
public function test_order_calculates_total_correctly()
{
    // Arrange
    $product = Product::factory()->create(['price' => 100]);
    $order = Order::factory()->create();
    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // Act
    $total = $order->calculateTotal();

    // Assert
    $this->assertEquals(200, $total, 'Order total should equal price * quantity');
}
```

## Factory Usage (Critical)

### Always Use Factories

**Never use direct model creation**. **Always use factories.**

```php
// ✅ CORRECT - Using factory
$user = User::factory()->create([
    'email' => 'test@example.com'
]);

$post = Post::factory()
    ->for($user)
    ->create();

// ❌ WRONG - Direct creation
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);
```

### Factory Relationships

Use `for()` and `has()` for relationships:

```php
// ✅ CORRECT
$post = Post::factory()
    ->for($user)
    ->has(Comment::factory()->count(3))
    ->create();

// With specific relationship name
$comment = Comment::factory()
    ->for(User::factory(), 'author')
    ->for($post)
    ->create();
```

### Recycle Pattern

Use `recycle()` to reuse models across relationships:

```php
// ✅ CORRECT - Reuse models
$user = User::factory()->create();

$posts = Post::factory()
    ->count(3)
    ->recycle($user)
    ->create();

$comments = Comment::factory()
    ->count(5)
    ->recycle($user)  // Same user for all comments
    ->recycle($posts) // Distribute across the posts
    ->create();
```

### Never Use Hardcoded IDs

```php
// ✅ CORRECT - Relationships through factories
$order = Order::factory()
    ->for($customer)
    ->create();

// ❌ WRONG - Hardcoded IDs
$order = Order::create([
    'customer_id' => 1,
    'status' => 'pending',
]);
```

## Assertions

### Use Specific Assertions

Choose assertions that clearly communicate intent:

```php
// ✅ CORRECT - Specific and clear
$this->assertEquals(200, $response->status());
$this->assertNull($user->deleted_at);
$this->assertCount(3, $orders);
$this->assertInstanceOf(Order::class, $result);
$this->assertDatabaseHas('orders', ['status' => 'completed']);
$this->assertDatabaseMissing('orders', ['status' => 'cancelled']);

// ❌ WRONG - Generic assertions
$this->assertTrue($response->status() == 200);
$this->assertTrue($user->deleted_at === null);
$this->assertTrue(count($orders) === 3);
```

### Common Assertion Types

| Assertion | Usage |
|-----------|-------|
| `assertEquals` | Check for equality |
| `assertSame` | Check for identity (===) |
| `assertNull` / `assertNotNull` | Check null values |
| `assertTrue` / `assertFalse` | Check boolean values |
| `assertCount` | Check collection/array sizes |
| `assertEmpty` / `assertNotEmpty` | Check if empty |
| `assertInstanceOf` | Check object types |
| `assertDatabaseHas` / `assertDatabaseMissing` | Check database state |
| `assertJson` | Check JSON responses |
| `assertJsonStructure` | Validate JSON structure |

### Assertion Messages

Always provide meaningful failure messages:

```php
// ✅ CORRECT
$this->assertEquals(
    100,
    $order->total,
    'Order total should match the sum of item prices'
);

// ❌ WRONG - No context on failure
$this->assertEquals(100, $order->total);
```

## Mocking and Stubbing

### Use Mocks for External Services

```php
// ✅ CORRECT - Mock external service
$paymentGateway = $this->mock(PaymentGateway::class);
$paymentGateway->shouldReceive('charge')
    ->once()
    ->with($amount, $customer)
    ->andReturn(['status' => 'success']);

// Perform action
$result = $service->processPayment($amount, $customer);

// Assert
$this->assertEquals('success', $result['status']);
```

### Use Laravel Fakes

```php
// ✅ CORRECT - Use built-in fakes
Mail::fake();

// ... test code that sends email ...

Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
    return $mail->order->id === $order->id;
});

// Other fakes
Queue::fake();
Storage::fake('s3');
Event::fake();
Notification::fake();
Bus::fake();
```

### Don't Mock What You Don't Own

```php
// ✅ CORRECT - Use real Eloquent models
$user = User::factory()->create();

// ❌ WRONG - Mocking framework classes
$user = $this->mock(User::class);
```

## Testing Events and Jobs

### Events

```php
Event::fake();

// Perform action
$order->complete();

// Assert event was dispatched
Event::assertDispatched(OrderCompleted::class, function ($event) use ($order) {
    return $event->order->id === $order->id;
});

// Assert event was not dispatched
Event::assertNotDispatched(OrderCancelled::class);
```

### Jobs

```php
Queue::fake();

// Perform action
$order->process();

// Assert job was pushed
Queue::assertPushed(ProcessOrder::class, function ($job) use ($order) {
    return $job->order->id === $order->id;
});

// Assert job count
Queue::assertPushed(ProcessOrder::class, 1);
```

## Testing Exceptions

```php
public function test_order_cannot_be_completed_when_payment_fails()
{
    $this->expectException(PaymentFailedException::class);
    $this->expectExceptionMessage('Payment was declined');

    $order = Order::factory()->create();
    $order->completeWithPayment($invalidCard);
}
```

## Complete Example: Before and After

### Before (Anti-pattern)

```php
public function test_order()
{
    // Direct creation with hardcoded values
    $user = User::create([
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => bcrypt('password')
    ]);

    $product = Product::create([
        'name' => 'Widget',
        'price' => 100
    ]);

    $order = Order::create([
        'user_id' => 1,  // Hardcoded!
        'total' => 100
    ]);

    // Unclear assertion
    $this->assertEquals($order->total, 100);
}
```

### After (Best Practice)

```php
/**
 * Test that order total is calculated from items
 */
public function test_order_total_is_calculated_from_items()
{
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);

    $order = Order::factory()
        ->for($user)
        ->create();

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => $product->price,
    ]);

    // Act
    $total = $order->calculateTotal();

    // Assert
    $this->assertEquals(
        200,
        $total,
        'Order total should equal sum of (price × quantity) for all items'
    );
}
```

## Key Improvements

1. ✅ Descriptive test name explaining what, when, and expected outcome
2. ✅ Using factories for all model creation
3. ✅ No hardcoded IDs
4. ✅ Clear Arrange-Act-Assert structure
5. ✅ Meaningful assertion message
6. ✅ Explicit variable naming

## Data Providers

Use data providers for testing multiple scenarios:

```php
/**
 * @dataProvider invalidEmailProvider
 */
public function test_registration_fails_with_invalid_email($email)
{
    $response = $this->post('/register', [
        'email' => $email,
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
}

public static function invalidEmailProvider(): array
{
    return [
        'missing @' => ['notanemail.com'],
        'missing domain' => ['user@'],
        'spaces' => ['user @domain.com'],
        'empty' => [''],
    ];
}
```

## Test Execution

```bash
# All tests
php artisan test

# Specific file
php artisan test tests/Feature/OrderTest.php

# Specific test method
php artisan test --filter=test_order_total_is_calculated_from_items

# With coverage
php artisan test --coverage

# Parallel execution
php artisan test --parallel
```

## Workflow Checklist

When writing tests:

1. ✅ Write descriptive test name following convention
2. ✅ Add PHPDoc explaining the test
3. ✅ Use Arrange-Act-Assert structure
4. ✅ Use factories exclusively (no direct creation)
5. ✅ Use `recycle()` for shared models
6. ✅ Use specific assertions with messages
7. ✅ Use Laravel fakes for external systems
8. ✅ Run tests to verify they pass
9. ✅ Check test coverage if needed

## Final Reminder

- **ALWAYS use factories** - Never use direct model creation
- **NEVER hardcode IDs** - Use relationships
- **USE specific assertions** with meaningful messages
- **FOLLOW Arrange-Act-Assert** pattern
- **ADD PHPDoc** to every test method
- **USE Laravel fakes** for mail, queue, storage, etc.
- **MOCK external services** only, not framework classes
- **RUN tests** to ensure they pass

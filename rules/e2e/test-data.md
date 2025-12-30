---
paths: e2e/**/*.ts
---

# E2E Test Data Management

Test data is managed via dedicated API endpoints that are **only available in local/testing environments**.

## Architecture Overview

Instead of relying on seeded data or manipulating the database directly, E2E tests create and clean up data through a dedicated E2E API:

```
Test                           Backend
┌─────────────┐               ┌─────────────────────────────────┐
│ Playwright  │   HTTP/JSON   │  app/Http/Controllers/E2E/      │
│             │──────────────→│  ├── UserController             │
│ POST        │               │  ├── OrderController            │
│ /e2e/users  │               │  └── ...                        │
│             │←──────────────│  User::factory()->create()      │
└─────────────┘               └─────────────────────────────────┘
```

**Benefits:**
- **Test isolation** - Each test creates its own data
- **Parallel execution** - Tests don't interfere with each other
- **Clean state** - No leftover data between runs
- **Uses factories** - Realistic data via Laravel factories

## Backend Setup

### 1. Controller Structure

Create **one controller per model** in `app/Http/Controllers/E2E/`:

```
app/Http/Controllers/E2E/
├── UserController.php      # User CRUD for tests
├── OrderController.php     # Order CRUD for tests
└── ProductController.php   # Product CRUD for tests
```

**Why separate controllers?**
- Follows RESTful conventions
- Easier to maintain and extend
- Clear responsibility per model
- Matches how your main app is organized

### 2. Routes File (`routes/e2e.php`)

```php
<?php

use App\Http\Controllers\E2E\UserController;
use App\Http\Controllers\E2E\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E2E Testing Routes
|--------------------------------------------------------------------------
|
| These routes are ONLY available in local and testing environments.
| They provide endpoints for Playwright to create/cleanup test data.
|
*/

Route::prefix('e2e')->group(function () {
    // User management
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Order management (example of nested resource)
    Route::post('/users/{user}/orders', [OrderController::class, 'store']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Add more resources as needed
});
```

### 3. Register Routes (Local Only)

In `RouteServiceProvider.php`:

```php
public function boot()
{
    $this->routes(function () {
        // ... other routes ...

        // E2E Testing routes - ONLY in local/testing environments
        if (app()->environment('local', 'testing')) {
            Route::middleware('api')  // 'api' to avoid CSRF
                ->namespace($this->namespace)
                ->group(base_path('routes/e2e.php'));
        }
    });
}
```

### 4. Example Controller (`app/Http/Controllers/E2E/UserController.php`)

```php
<?php

namespace App\Http\Controllers\E2E;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * E2E test controller for User management.
 *
 * IMPORTANT: Only available in local and testing environments.
 */
class UserController extends Controller
{
    /**
     * Create a user for E2E testing.
     *
     * POST /e2e/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
        ]);

        $user = User::factory()->create([
            'name' => $validated['name'] ?? 'E2E Test User',
            'email' => $validated['email'] ?? 'e2e-' . Str::random(12) . '@test.example.com',
            'password' => bcrypt('password'),
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',  // Plain text for tests
        ], 201);
    }

    /**
     * Delete a user and associated data.
     *
     * DELETE /e2e/users/{user}
     */
    public function destroy(User $user): JsonResponse
    {
        $user->forceDelete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
```

## Frontend Setup

### 1. Test Data Helper (`e2e/fixtures/test-data.ts`)

```typescript
import { APIRequestContext } from '@playwright/test'

export interface UserResponse {
  id: number
  name: string
  email: string
  password: string
}

export interface TestData {
  user: UserResponse
}

/**
 * Helper for managing E2E test data via the E2E API.
 */
export class TestDataHelper {
  private request: APIRequestContext
  private baseURL: string
  private userId: number | null = null

  constructor(request: APIRequestContext, baseURL = 'http://localhost:8081') {
    this.request = request
    this.baseURL = baseURL
  }

  async createUser(options?: { name?: string; email?: string }): Promise<UserResponse> {
    const response = await this.request.post(`${this.baseURL}/e2e/users`, {
      data: {
        name: options?.name,
        email: options?.email,
      },
    })

    if (!response.ok()) {
      const text = await response.text()
      throw new Error(`Failed to create user: ${response.status()} - ${text}`)
    }

    const user = await response.json() as UserResponse
    this.userId = user.id
    return user
  }

  async deleteUser(userId: number): Promise<void> {
    const response = await this.request.delete(`${this.baseURL}/e2e/users/${userId}`)

    if (!response.ok()) {
      const text = await response.text()
      throw new Error(`Failed to delete user: ${response.status()} - ${text}`)
    }
  }

  async createTestData(options?: { userName?: string }): Promise<TestData> {
    const user = await this.createUser({ name: options?.userName })
    return { user }
  }

  async cleanup(): Promise<void> {
    if (this.userId) {
      await this.deleteUser(this.userId)
      this.userId = null
    }
  }
}
```

### 2. Base Fixture (`e2e/fixtures/base.fixture.ts`)

```typescript
import { test as base, expect, Page } from '@playwright/test'
import { TestDataHelper, TestData } from './test-data'

export const test = base.extend<{
  testDataHelper: TestDataHelper
  testData: TestData
  authenticatedPage: { page: Page; testData: TestData }
}>({
  /**
   * Manual test data control.
   */
  testDataHelper: async ({ request }, use) => {
    const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8081'
    const helper = new TestDataHelper(request, baseURL)
    await use(helper)
    await helper.cleanup()
  },

  /**
   * Pre-created test data with automatic cleanup.
   */
  testData: async ({ request }, use) => {
    const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8081'
    const helper = new TestDataHelper(request, baseURL)
    const data = await helper.createTestData()
    await use(data)
    await helper.cleanup()
  },

  /**
   * Page logged in as a test user with automatic cleanup.
   */
  authenticatedPage: async ({ page, request }, use) => {
    const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:8081'
    const helper = new TestDataHelper(request, baseURL)

    // Create test data
    const testData = await helper.createTestData()

    // Log in
    await page.goto('/login')
    await page.getByLabel(/email/i).fill(testData.user.email)
    await page.getByLabel(/password/i).fill(testData.user.password)
    await page.getByRole('button', { name: /log in/i }).click()

    // Wait for auth to complete (adjust URL pattern for your app)
    await page.waitForURL('**/dashboard**', { timeout: 15000 })

    await use({ page, testData })

    await helper.cleanup()
  },
})

export { expect }
export type { TestData, TestDataHelper } from './test-data'
```

## Using Fixtures

### `authenticatedPage` (Recommended)

For most tests - provides logged-in page with test data:

```typescript
import { test, expect } from '../../fixtures/base.fixture'

test('user sees their name', async ({ authenticatedPage }) => {
  const { page, testData } = authenticatedPage

  // page is already logged in
  await page.goto('/profile')
  await expect(page.getByText(testData.user.name)).toBeVisible()
})
```

### `testData` (No Login)

When you need data but want to handle login yourself:

```typescript
test('login flow', async ({ page, testData }) => {
  await page.goto('/login')
  await page.getByLabel(/email/i).fill(testData.user.email)
  await page.getByLabel(/password/i).fill(testData.user.password)
  await page.getByRole('button', { name: /log in/i }).click()

  await expect(page).toHaveURL(/.*\/dashboard/)
})
```

### `testDataHelper` (Manual Control)

For complex scenarios:

```typescript
test('admin can see other users', async ({ page, testDataHelper }) => {
  const admin = await testDataHelper.createUser({ name: 'Admin' })
  const user = await testDataHelper.createUser({ name: 'Regular User' })

  // Login as admin, verify can see user...

  // Manual cleanup
  await testDataHelper.deleteUser(admin.id)
  await testDataHelper.deleteUser(user.id)
})
```

## Extending for Your Project

### Adding a New Entity Type

**1. Create a new controller** (`app/Http/Controllers/E2E/PostController.php`):

```php
<?php

namespace App\Http\Controllers\E2E;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * POST /e2e/users/{user}/posts
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
        ]);

        $post = Post::factory()->create([
            'title' => $validated['title'] ?? 'E2E Test Post',
            'user_id' => $user->id,
        ]);

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'user_id' => $post->user_id,
        ], 201);
    }

    /**
     * DELETE /e2e/posts/{post}
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->forceDelete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}
```

**2. Add routes** (`routes/e2e.php`):

```php
use App\Http\Controllers\E2E\PostController;

Route::prefix('e2e')->group(function () {
    // ... existing routes ...

    // Posts (nested under users)
    Route::post('/users/{user}/posts', [PostController::class, 'store']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});
```

**3. Add TypeScript helper method** (`e2e/fixtures/test-data.ts`):

```typescript
export interface PostResponse {
  id: number
  title: string
  user_id: number
}

// In TestDataHelper class:
async createPost(userId: number, options?: { title?: string }): Promise<PostResponse> {
  const response = await this.request.post(`${this.baseURL}/e2e/users/${userId}/posts`, {
    data: { title: options?.title },
  })

  if (!response.ok()) {
    throw new Error(`Failed to create post: ${response.status()}`)
  }

  return await response.json() as PostResponse
}

async deletePost(postId: number): Promise<void> {
  const response = await this.request.delete(`${this.baseURL}/e2e/posts/${postId}`)

  if (!response.ok()) {
    throw new Error(`Failed to delete post: ${response.status()}`)
  }
}
```

## Security

**Testing routes are protected by environment check:**

```php
if (app()->environment('local', 'testing')) {
    // Routes only registered here
}
```

- **Never available in production**
- Uses `api` middleware (no CSRF, but rate limited)
- No authentication required (for test simplicity)

## Key Principles

1. **Environment Guard** - Routes must NOT exist in production
2. **Use Factories** - Leverage Laravel factories for realistic data
3. **CRUD Pattern** - Create and delete endpoints for each entity
4. **Cascade Deletes** - Delete should clean up related data
5. **Automatic Cleanup** - Fixtures clean up after each test
6. **Plain Text Password** - Return password in response for login tests

# Writing Style Guidelines

This file defines how to write clear, consistent, and useful documentation.

---

## Core Principles

1. **Write for developers new to the codebase** - Don't assume context
2. **Explain "why" not just "what"** - Design decisions matter more than code details
3. **Be concise but complete** - Every sentence should add value
4. **Use examples** - Show, don't just tell
5. **Link generously** - Connect related concepts

---

## Clarity

### Audience

Write for a developer who:
- Is competent but new to this codebase
- Knows the technology stack (Laravel, Vue, etc.)
- Doesn't know your specific business logic or architectural decisions

### Language

**Do:**
- ✅ Use simple, direct language
- ✅ Define jargon on first use
- ✅ Use active voice ("The service validates..." not "Validation is performed...")
- ✅ Be specific ("JWT tokens expire after 1 hour" not "tokens expire quickly")
- ✅ Use present tense ("The system sends..." not "The system will send...")

**Don't:**
- ❌ Use unnecessarily complex words
- ❌ Use abbreviations without definition
- ❌ Use passive voice excessively
- ❌ Be vague or hand-wavy
- ❌ Use future tense for current functionality

### Examples

**Bad:**
```markdown
The authentication mechanism leverages JWT paradigms to facilitate
stateless session management across distributed endpoints.
```

**Good:**
```markdown
Authentication uses JWT tokens for stateless sessions. Tokens are validated
on each request without server-side session storage, making the system
scalable across multiple servers.
```

---

## Structure

### Heading Levels

Use consistent heading hierarchy:

```markdown
# Document Title (H1 - once per file)

## Main Section (H2)

### Subsection (H3)

#### Detail (H4 - use sparingly)
```

**Don't skip levels** (e.g., don't go from H2 to H4).

### Document Organization

**Every documentation file should have:**

1. **Title** - Clear, descriptive H1
2. **Overview/Purpose** - 2-3 sentences explaining what this is
3. **Main content** - Organized into logical sections
4. **Code references** - Links to actual code
5. **Related documentation** - Links to connected docs

### Section Order

**For feature docs:**
1. What It Does (user perspective)
2. How It Works (system perspective)
3. Key Components
4. Implementation Details
5. Tests
6. Code References

**For component docs:**
1. Purpose
2. Public API
3. Usage Examples
4. Configuration
5. Implementation Notes
6. Tests

### Lists and Tables

**Use lists for:**
- Steps in a process
- Multiple related items
- Options or choices

**Use tables for:**
- Comparisons
- Parameter documentation
- Mapping relationships

**Example table:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `userId` | string | Yes | The user's ID |
| `includeDeleted` | boolean | No | Include soft-deleted records |

---

## Code References

### Format

Always use this format:
```markdown
`path/to/file.php:line-number` - Description
```

### Examples

**Single line:**
```markdown
- `app/Services/Auth/JwtValidator.php:45` - Token signature validation
```

**Line range:**
```markdown
- `app/Services/Auth/JwtValidator.php:45-67` - Complete JWT validation logic
```

**Multiple references:**
```markdown
- `app/Services/Auth/JwtValidator.php:45` - Main validation method
- `app/Services/Auth/SessionManager.php:23` - Session storage
- `app/Models/User.php:142` - Token generation
```

### What to Reference

**Do reference:**
- ✅ Main entry points
- ✅ Core business logic
- ✅ Complex algorithms
- ✅ Configuration locations
- ✅ Test files

**Don't reference:**
- ❌ Every single line of code
- ❌ Obvious getters/setters
- ❌ Standard framework code
- ❌ Third-party library internals

---

## Completeness

### Cover Key Aspects

**Every feature doc should address:**
- What the feature does
- How it works (high-level)
- Key components involved
- Security considerations (if applicable)
- Performance considerations (if relevant)
- Edge cases and error handling
- Related tests

**Every component doc should address:**
- Purpose and responsibilities
- Public API (methods, props, events)
- Usage examples
- Dependencies
- Configuration (if any)
- Implementation notes

### Include Examples

**Good examples:**
- Show common use cases
- Include comments explaining non-obvious parts
- Are copy-pasteable (when appropriate)
- Show both success and error cases (when relevant)

**Bad examples:**
- Overly simplified pseudo-code
- Missing context
- Incorrect or outdated code
- No explanation of what's happening

### Address Common Questions

Think about what developers will ask:
- "How do I use this?"
- "What happens if X fails?"
- "Can I customize Y?"
- "What are the performance implications?"
- "Where are the tests?"

Answer these proactively in the documentation.

---

## Code Blocks

### Formatting

```markdown
```php
// PHP code with syntax highlighting
$service = new AuthService();
$result = $service->validate($token);
```  // (closing backticks)

```vue
<!-- Vue code with syntax highlighting -->
<template>
  <div>Example</div>
</template>
```  // (closing backticks)
```

### When to Use

**Use code blocks for:**
- Usage examples
- Configuration examples
- Request/response examples
- Error handling patterns

**Don't use code blocks for:**
- Single method names (use inline `code`)
- File paths (use inline `code`)
- Simple values (use inline `code`)

---

## Linking

### Internal Links

**Link to:**
- Related features
- Related components
- Domain overviews
- Architecture docs

**Format:**
```markdown
[Link Text](relative/path/to/file.md)
[Authentication Flow](../../domains/authentication/features/login.md)
[AuthService](../../layers/backend/services/auth-service.md)
```

### External Links

**Link to:**
- Official documentation
- RFCs or specifications
- Relevant blog posts or articles
- GitHub issues (for context)

**Format:**
```markdown
[Laravel Documentation](https://laravel.com/docs/authentication)
[JWT RFC 7519](https://tools.ietf.org/html/rfc7519)
```

### Link Density

**Do:**
- ✅ Link on first mention of a concept
- ✅ Link to code when referencing it
- ✅ Link to tests
- ✅ Link to related docs in "Related Documentation" section

**Don't:**
- ❌ Over-link (same link multiple times in one section)
- ❌ Link to obvious things developers already know
- ❌ Create orphaned documentation (not linked from anywhere)

---

## Tone and Voice

### Professional but Approachable

**Good:**
```markdown
The AuthService handles JWT token validation. When a token fails validation,
the service throws an AuthenticationException, which is caught by the
Authenticate middleware and returns a 401 response.
```

**Too formal:**
```markdown
It shall be noted that the AuthService component is responsible for the
validation of JWT tokens in accordance with RFC 7519 specifications.
```

**Too casual:**
```markdown
So basically the AuthService checks if your JWT is legit, and if it's not,
it's gonna throw an error and you'll get a 401 lol.
```

### Avoid Superlatives

**Don't:**
- ❌ "This amazing service..."
- ❌ "The powerful component..."
- ❌ "Simply call the method..."
- ❌ "Just add this line..."

**Do:**
- ✅ State facts objectively
- ✅ Describe what things do
- ✅ Explain complexity honestly

---

## Maintenance and Updates

### Timestamps

**Include timestamps for:**
- Time-sensitive information
- "As of" statements about current state
- Performance benchmarks

**Example:**
```markdown
As of 2025-11, JWT tokens expire after 1 hour (configurable in `config/jwt.php`).
```

### Version Information

**Include version info for:**
- Package-specific features
- Breaking changes
- Deprecated patterns

**Example:**
```markdown
This uses Laravel 10+ route model binding. For Laravel 9, use explicit queries.
```

### Future-Proofing

**Link to code as source of truth:**
```markdown
Token expiration is configured in `config/jwt.php:15`. See the code for current defaults.
```

**Note what might change:**
```markdown
The current implementation uses Stripe for payments. This may be abstracted
to support multiple payment providers in the future.
```

---

## Documentation vs Code Comments

### Documentation (docs/ files)

**Focus on:**
- Architecture and design decisions
- High-level flows
- Why things work the way they do
- How components interact
- Business rules and context

### Code Comments (in code files)

**Focus on:**
- Why specific implementation choices were made
- Non-obvious behavior
- Workarounds and gotchas
- TODOs and FIXMEs

**Don't duplicate:** If it's in the code comments, don't repeat it in docs. Link to the code instead.

---

## Examples of Good vs Bad Documentation

### Example 1: Component Purpose

**Bad:**
```markdown
# AuthService

This service does authentication stuff.
```

**Good:**
```markdown
# AuthService

The AuthService handles JWT token validation for API requests. It validates
token signatures, checks expiration, and extracts user claims. This service
is used by the Authenticate middleware on every authenticated API route.
```

### Example 2: Method Documentation

**Bad:**
```markdown
### validate()
Validates a token.
```

**Good:**
```markdown
### `validate(string $token): TokenPayload`

Validates a JWT token and returns its payload.

**Parameters:**
- `$token` (string) - The raw JWT token from the Authorization header

**Returns:** `TokenPayload` object containing user ID, expiration, and custom claims

**Throws:**
- `InvalidTokenException` - If token signature is invalid
- `ExpiredTokenException` - If token has expired
- `MalformedTokenException` - If token format is invalid

**Example:**
```php
try {
    $payload = $authService->validate($token);
    $userId = $payload->getUserId();
} catch (ExpiredTokenException $e) {
    // Handle expired token
}
```  // (closing backticks)
```

### Example 3: Feature Overview

**Bad:**
```markdown
# Login

Users can log in with email and password. It works with JWT.
```

**Good:**
```markdown
# User Login

## What It Does

Users authenticate with email and password. Upon successful authentication,
the system issues a JWT token valid for 24 hours. This token is used for
subsequent API requests.

## How It Works

1. User submits email and password to `POST /api/login`
2. System validates credentials against hashed passwords in database
3. If valid, system generates JWT token with user ID and roles
4. System returns token and user profile data
5. Client stores token and includes it in Authorization header for future requests

## Security

- Passwords are hashed with bcrypt (cost factor: 12)
- Failed login attempts are rate-limited (5 attempts per 15 minutes)
- Tokens are signed with RS256 using private key from `config/jwt.php`
- Tokens cannot be revoked (stateless design trade-off)
```

---

## Quick Checklist

Before considering documentation complete:

- [ ] Written for developers new to the codebase
- [ ] Explains "why" not just "what"
- [ ] Uses clear, direct language
- [ ] Includes relevant code references with line numbers
- [ ] Links to related documentation
- [ ] Includes usage examples where helpful
- [ ] Uses consistent heading levels
- [ ] Covers key aspects (security, errors, edge cases if applicable)
- [ ] Answers common questions proactively

---

## Related Files

- **templates.md** - What structure to use
- **common-mistakes.md** - Things to avoid
- **index-maintenance.md** - Making docs discoverable

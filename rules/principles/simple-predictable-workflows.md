---
paths:
  - app/**/*.php
  - resources/js/**/*.{vue,ts,tsx}
---

# Simple, Predictable Workflows (No Cascading Fallbacks)

## 🚨 STOP: Are You Cascading?

**Before implementing, ask yourself these questions:**

1. Do I have multiple methods/approaches that solve the same problem differently?
2. Am I checking if one approach failed, then trying another?
3. Does my code have 3+ conditional branches trying different data sources?
4. Am I mixing validation attributes AND manual rules in the same class?

**If YES to any → You're cascading. Pick ONE approach.**

---

**Core Principle:** Code should follow ONE clear path. When a user configures something, honor it or fail. Never silently cascade through alternatives to "make it work."

## The Anti-Pattern: Cascading Fallbacks

Claude tends to generate "overly resilient" code that tries multiple approaches to succeed:

```php
// ❌ ANTI-PATTERN - Cascading fallbacks
protected function getConversionDate(Config $config, Context $context): Carbon
{
    // Try method 1
    if ($config->date_column_key !== null) {
        $date = $context[$config->date_column_key] ?? null;
        if ($date !== null) {
            return Carbon::parse($date);
        }
        // Silently continue to fallback...
    }

    // Try method 2
    if ($config->date_source !== null) {
        $date = match ($config->date_source) {
            'ext_created_at' => $context['ext_created_at'] ?? null,
            'created_at' => $context['created_at'] ?? null,
        };
        if ($date !== null) {
            return $date;
        }
        // Silently continue to fallback...
    }

    // Try method 3
    if (isset($context['ext_created_at'])) {
        return $context['ext_created_at'];
    }

    // Try method 4
    if (isset($context['created_at'])) {
        return $context['created_at'];
    }

    // Final fallback
    return Carbon::now();
}
```

### Why This Is Wrong

1. **Unpredictable** - Which path was taken? You have to trace through 4+ conditions to know
2. **Hidden failures** - User configures `date_column_key`, it's empty, system silently uses `now()` instead
3. **Hard to debug** - "Why is it using today's date?" requires understanding the entire cascade
4. **False confidence** - System appears to work but produces wrong results
5. **Wrong philosophy** - The code's goal became "succeed at any cost" instead of "do what the user asked"

## The Correct Pattern: Explicit Branches

Each configuration choice should be a **separate, mutually exclusive branch** with clear failure modes:

```php
// ✅ CORRECT - Explicit branches, no cascading
protected function getConversionDate(
    Config $config,
    Context $context,
    ErrorBuilder $errors
): ?Carbon {
    // CASE 1: User explicitly configured a date column
    if ($config->date_column_key !== null) {
        $date = $context[$config->date_column_key] ?? null;

        if ($date === null) {
            $errors->add("Column '{$config->date_column_key}' has no value");
            return null;  // FAIL - don't cascade
        }

        return Carbon::parse($date);
    }

    // CASE 2: User explicitly configured a date source
    if ($config->date_source !== null) {
        $date = match ($config->date_source) {
            'ext_created_at' => $context['ext_created_at'],
            'created_at' => $context['created_at'],
        };

        if ($date === null) {
            $errors->add("Source '{$config->date_source}' has no value");
            return null;  // FAIL - don't cascade
        }

        return $date;
    }

    // CASE 3: No configuration - NOW we can use sensible defaults
    return $context['ext_created_at']
        ?? $context['created_at']
        ?? Carbon::now();
}
```

### Why This Is Right

1. **Predictable** - Each case is clearly separated
2. **Explicit failures** - Configured options that fail produce errors
3. **Easy to debug** - You can tell exactly which branch was taken
4. **Correct philosophy** - "Do what the user asked, or tell them it failed"
5. **Defaults only when appropriate** - Fallback logic only applies when nothing is configured

## TypeScript Example

The same principle applies in frontend code:

```typescript
// ❌ ANTI-PATTERN - Cascading fallbacks
function getDisplayValue(config: Config, data: Data): string {
  if (config.customFormatter) {
    const result = config.customFormatter(data);
    if (result) return result;
    // Silently continue...
  }

  if (config.fieldPath) {
    const value = get(data, config.fieldPath);
    if (value !== undefined) return String(value);
    // Silently continue...
  }

  return data.name ?? data.id ?? 'Unknown';
}

// ✅ CORRECT - Explicit branches
function getDisplayValue(config: Config, data: Data): string | null {
  // CASE 1: Custom formatter configured
  if (config.customFormatter) {
    const result = config.customFormatter(data);
    if (!result) {
      console.error(`Custom formatter returned empty for ${data.id}`);
      return null;  // FAIL - don't cascade
    }
    return result;
  }

  // CASE 2: Field path configured
  if (config.fieldPath) {
    const value = get(data, config.fieldPath);
    if (value === undefined) {
      console.error(`Field '${config.fieldPath}' not found in data`);
      return null;  // FAIL - don't cascade
    }
    return String(value);
  }

  // CASE 3: No configuration - use defaults
  return data.name ?? data.id ?? 'Unknown';
}
```

## Key Rules

### 1. Explicit Configuration = Explicit Failure

If a property is set (not null), the code MUST:
- Use that configuration, OR
- Return an error explaining why it couldn't

**Never** silently fall through to an alternative.

### 2. One Path Per Case

Structure code as mutually exclusive cases:

```php
if ($config->hasOptionA()) {
    // Handle A completely - succeed or fail
    return ...;
}

if ($config->hasOptionB()) {
    // Handle B completely - succeed or fail
    return ...;
}

// Default case - only if nothing configured
return defaultBehavior();
```

### 3. Cascading Defaults Only When Nothing Configured

Fallback chains are acceptable **only** in the default case:

```php
// ✅ OK - Cascading defaults when nothing is configured
if (!$config->hasExplicitSetting()) {
    return $context['preferred'] ?? $context['alternative'] ?? 'default';
}
```

### 4. Ask Yourself: "Can I Tell Which Path Was Taken?"

If the answer is "I'd have to trace through multiple conditions," the code needs refactoring.

## Anti-Pattern Smells

Watch for these patterns that indicate cascading fallbacks:

```php
// 🚩 SMELL: Conditional that checks result, then continues
if ($value !== null) {
    return $value;
}
// continues to next approach...

// 🚩 SMELL: Multiple null-coalescing in configured path
return $config->getValue() ?? $alternative ?? $anotherAlternative ?? $default;

// 🚩 SMELL: Try-catch that swallows error and tries something else
try {
    return $this->methodA();
} catch (Exception $e) {
    return $this->methodB();  // Silent fallback
}

// 🚩 SMELL: "Priority" or "resolution" chains in comments
// Priority: option1 -> option2 -> option3 -> default
```

## Summary

**Don't write code that "succeeds at any cost."** Write code that:
1. Does exactly what was configured
2. Fails explicitly when that's not possible
3. Uses defaults only when nothing was configured
4. Is easy to trace and debug

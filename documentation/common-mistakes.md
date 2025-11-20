# Common Mistakes and Validation

This file lists common documentation mistakes and provides a validation checklist.

---

## Common Mistakes to Avoid

### 1. Not Updating INDEX.md

❌ **Mistake:** Creating documentation without adding an entry to INDEX.md

**Why it's bad:** Documentation becomes undiscoverable. Developers won't know it exists.

**Solution:** Always update INDEX.md in the same commit as creating new documentation.

---

### 2. Inconsistent File Naming

❌ **Mistake:** Using CamelCase, snake_case, or mixed conventions

**Examples of wrong names:**
- `AuthService.md` (should be `auth-service.md`)
- `password_recovery.md` (should be `password-recovery.md`)
- `PaymentForm.MD` (should be `payment-form.md`)

**Solution:** Always use lowercase-with-hyphens.md

---

### 3. Wrong File Placement

❌ **Mistake:** Putting documentation in the wrong directory

**Examples:**
- Putting feature documentation in layers/ instead of domains/
- Putting component documentation in domains/ instead of layers/
- Not following file-to-doc mapping conventions

**Solution:** Read `file-mapping.md` before creating documentation.

---

### 4. Missing Code References

❌ **Mistake:** Documenting without linking to actual code

**Bad example:**
```markdown
The AuthService validates tokens using JWT.
```

**Good example:**
```markdown
The AuthService validates tokens using JWT (`app/Services/Auth/JwtValidator.php:45`).
```

**Solution:** Include file paths with line numbers for all references.

---

### 5. Duplicate Information

❌ **Mistake:** Repeating the same information across multiple documentation files

**Why it's bad:** Leads to inconsistencies when code changes.

**Solution:** Document once, link everywhere else.

**Example:**
```markdown
<!-- In auth-service.md -->
JWT tokens expire after 1 hour (configured in `config/jwt.php:15`).

<!-- In login.md -->
After login, you receive a JWT token. See [AuthService](../../layers/backend/services/auth-service.md) for token details.
```

---

### 6. Orphaned Documentation

❌ **Mistake:** Creating documentation that isn't linked from anywhere

**Why it's bad:** If it's not linked, it doesn't exist.

**Solution:**
- Add to INDEX.md
- Link from parent README.md
- Link from related documentation

---

### 7. Missing Templates

❌ **Mistake:** Not using the standard templates

**Why it's bad:** Creates inconsistent documentation structure.

**Solution:** Use templates from `templates.md` as starting points.

---

### 8. Code-Level Details in Docs

❌ **Mistake:** Documenting every line of code instead of architecture

**Bad example:**
```markdown
First the method checks if the token is null, then it splits the token
by dots, then it checks if there are three parts...
```

**Good example:**
```markdown
The method validates JWT structure and signature using RS256. If invalid,
it throws InvalidTokenException. See `app/Services/Auth/JwtValidator.php:45-67`
for implementation details.
```

**Solution:** Focus on "why" and architecture, not line-by-line code explanation.

---

### 9. Vague Descriptions

❌ **Mistake:** Using generic or vague language

**Bad examples:**
- "This service handles stuff"
- "The component does things with users"
- "Manages data operations"

**Good examples:**
- "Validates JWT tokens and manages user sessions"
- "Displays user profile with editable fields"
- "Processes payment through Stripe API"

**Solution:** Be specific about what, how, and why.

---

### 10. Not Linking Related Docs

❌ **Mistake:** Failing to connect related documentation

**Solution:** Liberally link to:
- Related features
- Related components
- Domain overviews
- Architecture documents

---

### 11. Outdated Documentation

❌ **Mistake:** Not updating documentation when code changes

**Solution:**
- Update docs in the same PR as code changes
- Note time-sensitive information with dates
- Link to code as source of truth

---

### 12. Wrong Heading Levels

❌ **Mistake:** Skipping heading levels or using them inconsistently

**Bad example:**
```markdown
# Title
#### Subsection (skipped H2 and H3)
```

**Good example:**
```markdown
# Title
## Main Section
### Subsection
```

**Solution:** Use H1 for title, H2 for main sections, H3 for subsections, H4 sparingly.

---

## Validation Checklist

Before considering documentation complete, verify:

### File and Structure
- [ ] File is in correct location per `file-mapping.md`
- [ ] File name uses lowercase-with-hyphens.md format
- [ ] Used appropriate template from `templates.md`
- [ ] Heading hierarchy is correct (no skipped levels)

### Content Quality
- [ ] Title is clear and descriptive
- [ ] Overview/purpose section exists (2-3 sentences)
- [ ] Written for developers new to codebase
- [ ] Explains "why" not just "what"
- [ ] Uses clear, direct language
- [ ] No jargon without definition

### Code References
- [ ] Includes specific code references with line numbers
- [ ] Format: `path/to/file.php:123` or `path/to/file.php:45-67`
- [ ] References main entry points and core logic
- [ ] Doesn't reference every single line

### Completeness
- [ ] Covers key aspects (what, how, why)
- [ ] Includes usage examples where appropriate
- [ ] Addresses security considerations (if applicable)
- [ ] Addresses performance considerations (if relevant)
- [ ] Links to related tests
- [ ] Notes edge cases and error handling

### Links and Discoverability
- [ ] Added entry to `docs/INDEX.md`
- [ ] Updated timestamp in INDEX.md
- [ ] Updated parent README if adding to domain
- [ ] Links to related documentation
- [ ] Links use relative paths (not absolute)

### Style
- [ ] Uses active voice
- [ ] Uses present tense
- [ ] Be specific (not vague)
- [ ] Includes tables or lists for scannable content
- [ ] Code blocks use proper syntax highlighting

### Domain Documentation Specific
- [ ] Lists all features with links
- [ ] Includes architecture overview
- [ ] Lists related code directories
- [ ] Mentions API endpoints (if applicable)

### Feature Documentation Specific
- [ ] Explains what it does (user perspective)
- [ ] Explains how it works (system perspective)
- [ ] Lists key components (backend, frontend, database)
- [ ] Includes implementation details
- [ ] Links to tests

### Component Documentation Specific
- [ ] States clear purpose
- [ ] Documents public API (methods, props, events)
- [ ] Includes usage examples
- [ ] Lists dependencies
- [ ] Notes configuration options

---

## Quick Self-Review Questions

Ask yourself:

1. **Can a new developer understand this?**
   - If you joined today, would this documentation help you?

2. **Is it discoverable?**
   - Is it in INDEX.md?
   - Is it linked from related docs?

3. **Is it maintainable?**
   - Will this need updating every time code changes?
   - Have you linked to code instead of duplicating it?

4. **Is it complete?**
   - Have you answered the "what, how, why"?
   - Have you addressed common questions?

5. **Is it consistent?**
   - Does it follow the templates?
   - Does it match the style of existing docs?

---

## Red Flags

If you see any of these, fix them before committing:

🚩 No code references with line numbers
🚩 File not in INDEX.md
🚩 Wrong file naming (CamelCase, snake_case)
🚩 Documentation in wrong directory
🚩 Vague descriptions ("handles stuff", "manages data")
🚩 Missing purpose/overview section
🚩 No links to related documentation
🚩 Code-level implementation details instead of architecture
🚩 Duplicate information from other docs
🚩 No usage examples for components

---

## Fixing Common Issues

### Issue: Documentation is too technical

**Fix:** Rewrite focusing on "what" and "why" instead of "how". Link to code for implementation details.

### Issue: Documentation is too vague

**Fix:** Add specific examples, code references, and concrete use cases.

### Issue: Can't find where to put documentation

**Fix:** Read `structure.md` and `file-mapping.md`. If still unclear, ask in the team.

### Issue: Documentation is getting too long

**Fix:** Break into multiple files. Create domain/README.md with overview, then separate feature files.

### Issue: Duplicating information

**Fix:** Document once in the most appropriate place, link everywhere else.

---

## Related Files

- **structure.md** - Where things should be organized
- **file-mapping.md** - Where specific files should go
- **templates.md** - What structure to use
- **writing-style.md** - How to write clearly
- **index-maintenance.md** - How to make docs discoverable

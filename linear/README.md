# Linear Issue & Project Writing

**⚠️ MANDATORY:** Read this file before creating Linear projects or issues.

---

## Required Convention Files

Read these files in order based on what you're working on. Each file states when it should be read.

### 1. `creating-project.md` - READ FIRST

**When to read:** Before creating a new Linear project for a feature or initiative.

**Covers:** Project description structure, context depth, affected areas, architecture decisions, known limitations.

**Read now:** `.claude/rules/linear/creating-project.md`

---

### 2. `creating-issue.md`

**When to read:** Before writing individual Linear issues for development work.

**Covers:** Issue template, what to include/exclude, code snippets, acceptance criteria, helpful references.

**Read now:** `.claude/rules/linear/creating-issue.md`

---

### 3. `issue-lifecycle.md`

**When to read:** Before starting work on a Linear issue.

**Covers:** How to update issues during development, when to comment, marking issues complete, handling dependencies.

**Read now:** `.claude/rules/linear/issue-lifecycle.md`

---

### 4. `issue-template.md`

**When to read:** When you need a quick copy-paste template for creating an issue.

**Covers:** Ready-to-use issue template.

**Read now:** `.claude/rules/linear/issue-template.md`

---

### 5. Examples

**When to read:** When you need inspiration or want to see the conventions in practice.

**Files:**
- `.claude/rules/linear/examples/project-example.md` - Full project description example
- `.claude/rules/linear/examples/issue-examples.md` - Multiple issue examples (bug, feature, test, refactor)

---

## Quick Validation Checklist

Before creating an issue, scan for these red flags:

- [ ] Does the issue prescribe implementation details? → Should focus on outcomes
- [ ] Does it repeat conventions from `.claude/rules/backend` or `.claude/rules/frontend`? → Agent already knows those
- [ ] Does it specify exact file paths unnecessarily? → Agent can find files
- [ ] Is it missing acceptance criteria? → Should have testable outcomes
- [ ] Is it missing context about why this matters? → Should explain user impact
- [ ] Does it have a code snippet showing a bug without inline comments? → Should mark the problem clearly

**If you find any red flags, go back and read the relevant convention file.**

---

## Context for Claude Code Agents

When writing issues, remember that Claude Code agents will have:

✅ **What they WILL have:**
- All files in `.claude/rules/backend/` or `.claude/rules/frontend/`
- `CLAUDE.md` with project overview
- The Linear **project description** (shared context)
- The specific **issue description**
- Backend/Frontend reviewer subagents

❌ **What they will NOT have:**
- Your conversation history
- Investigation findings not documented
- The "why" behind architectural decisions (unless in project description)
- Business context (unless in project description)

**Therefore:** Issues should provide **domain knowledge and outcomes**, not implementation details or repeated conventions.

---

## How to Use

1. **For a new feature project:**
   - Read `creating-project.md`
   - Write comprehensive project description
   - Create Linear project with that description

2. **For individual issues:**
   - Read `creating-issue.md`
   - Use `issue-template.md` as starting point
   - Check examples in `examples/` for guidance
   - Write issue with clear outcomes and context

3. **While working on an issue:**
   - Read `issue-lifecycle.md`
   - Update issue when blocked or scope changes
   - Mark complete when all acceptance criteria met

4. **When in doubt:**
   - Check `examples/` for similar scenarios
   - Focus on outcomes, not implementation
   - Ask: "Would a new team member understand this?"
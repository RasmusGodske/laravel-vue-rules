---
paths:
  - "app/**/*.php"
  - "resources/js/**/*.{vue,ts,tsx}"
---

# Code Organization: Nested File Structures

## 🚨 CRITICAL: This Rule Exists Because It's Frequently Violated

**This is one of the most common mistakes.** The temptation is to quickly drop a file in the nearest directory and move on. **Resist this temptation.**

We don't want fast-written code. We want **future-proof code** that can be extended, maintained, and understood by others (and future you) for years to come.

**Every file needs a logical location.** If you can't immediately explain why a file belongs where you're putting it, stop and think harder about the structure.

---

## The Core Principle

> **Organize files in nested directories by domain/purpose, NEVER flat structures.**

A flat structure is a sign of lazy organization. It says "I didn't think about where this belongs, I just put it here." That's technical debt from day one.

---

## Why This Matters So Much

### 1. Codebases Grow Exponentially

What starts as 5 files becomes 50, then 500. A flat structure that "works fine" with 10 files becomes **impossible to navigate** with 100.

```
❌ BAD: "We only have a few data classes, flat is fine"
app/Data/
  UserData.php
  OrderData.php
  ProductData.php
  ReportConfigData.php
  ChartConfigData.php
  ...50 more files...
```

You can't find anything. You can't understand relationships. You can't onboard new developers.

### 2. Structure Communicates Intent

Directory structure is **documentation**. When someone sees:

```
app/Data/
  Reports/
    ReportConfigData.php
    ChartsConfigData.php
```

They immediately understand these classes are related to reports. No comments needed.

### 3. Prevents Naming Collisions

In flat structures, you end up with awkward prefixes:

```
❌ BAD: Flat with prefixes
app/Data/
  ReportConfigData.php
  ReportChartData.php
  ReportExportData.php
  UserProfileData.php
  UserSettingsData.php
  UserPreferencesData.php
```

vs.

```
✅ GOOD: Nested by domain
app/Data/
  Reports/
    ConfigData.php
    ChartData.php
    ExportData.php
  Users/
    ProfileData.php
    SettingsData.php
    PreferencesData.php
```

The nested version has cleaner, shorter names because the directory provides context.

### 4. Enables Feature-Based Development

When everything related to a feature is in one place, you can:
- Understand the feature by browsing one directory
- Refactor the feature without hunting across the codebase
- Delete the feature cleanly when it's no longer needed

---

## Directory Structure Patterns

### Eloquent Models

```
✅ GOOD - Nested by domain
app/Models/
  Commissions/
    Commission.php
    CommissionPlan.php
    CommissionCalculation.php
    CommissionCorrection.php
  DataSources/
    DataSource.php
    DataSourceColumn.php
    DataSourceRow.php
  Users/
    User.php
    CustomerUser.php
    Team.php

❌ BAD - Flat structure
app/Models/
  Commission.php
  CommissionPlan.php
  CommissionCalculation.php
  CommissionCorrection.php
  DataSource.php
  DataSourceColumn.php
  DataSourceRow.php
  User.php
  CustomerUser.php
  Team.php
```

### Data Classes

```
✅ GOOD - Nested by domain AND purpose
app/Data/
  Http/
    Controllers/
      CommissionController/
        ShowPropsData.php
        IndexPropsData.php
      ReportController/
        GenerateRequestData.php
        ShowPropsData.php
  Services/
    CommissionCalculation/
      CalculationResultData.php
      CalculationContextData.php
  Models/
    Commission/
      CommissionConfigData.php
      TriggerSettingsData.php

❌ BAD - Flat structure
app/Data/
  CommissionShowPropsData.php
  CommissionIndexPropsData.php
  ReportGenerateRequestData.php
  ReportShowPropsData.php
  CalculationResultData.php
  CalculationContextData.php
  CommissionConfigData.php
  TriggerSettingsData.php
```

### Vue Components

Components follow **two organizational patterns** depending on their scope:

#### Shared/Reusable Components → Group by Type

Generic components used across the app go in type-based directories:

```
✅ GOOD - Shared components by type
resources/js/Components/
  App/
    Forms/
      Input.vue
      Select.vue
      DatePicker.vue
      Checkbox.vue
    Modals/
      ConfirmDialog.vue
      EditDialog.vue
      DeleteConfirmation.vue
    Tables/
      DataTable.vue
      TablePagination.vue
      TableFilters.vue
    Navigation/
      Sidebar.vue
      Breadcrumbs.vue
      TabNav.vue
```

#### Domain-Specific Components → Group by Domain

Components that are specific to a feature/domain go in domain directories:

```
✅ GOOD - Domain-specific components by domain
resources/js/Components/
  Commissions/
    CommissionCalculationCard.vue
    CommissionPlanSelector.vue
    TriggerConfigurationForm.vue
    CommissionPreviewTable.vue
  Reports/
    ReportBuilder.vue
    ChartConfiguration.vue
    ReportExportOptions.vue
  DataSources/
    ColumnMappingTable.vue
    DataSourcePreview.vue
    ImportProgressIndicator.vue
```

#### Combined Example

A well-organized project has BOTH:

```
✅ GOOD - Combined structure
resources/js/Components/
  App/                          ← Shared/reusable components
    Forms/
      Input.vue
      Select.vue
    Modals/
      ConfirmDialog.vue
    Tables/
      DataTable.vue
  Commissions/                  ← Commission-specific components
    CommissionCalculationCard.vue
    CommissionPlanSelector.vue
    TriggerConfigurationForm.vue
  Reports/                      ← Report-specific components
    ReportBuilder.vue
    ChartConfiguration.vue
  DataSources/                  ← DataSource-specific components
    ColumnMappingTable.vue
    DataSourcePreview.vue

❌ BAD - Everything flat
resources/js/Components/App/
  Input.vue
  Select.vue
  ConfirmDialog.vue
  DataTable.vue
  CommissionCalculationCard.vue    ← Should be in Commissions/
  CommissionPlanSelector.vue       ← Should be in Commissions/
  TriggerConfigurationForm.vue     ← Should be in Commissions/
  ReportBuilder.vue                ← Should be in Reports/
  ChartConfiguration.vue           ← Should be in Reports/
  ColumnMappingTable.vue           ← Should be in DataSources/
```

**Rule:** If a component is only used within one domain/feature, it belongs in that domain's directory, not in a generic type directory.

### Services

```
✅ GOOD - Nested by domain
app/Services/
  Commissions/
    CommissionCalculationService.php
    CommissionCorrectionService.php
    CommissionExportService.php
  DataSources/
    DataSourceImportService.php
    DataSourceValidationService.php
  Reports/
    ReportGenerationService.php
    ReportExportService.php

❌ BAD - Flat structure
app/Services/
  CommissionCalculationService.php
  CommissionCorrectionService.php
  CommissionExportService.php
  DataSourceImportService.php
  DataSourceValidationService.php
  ReportGenerationService.php
  ReportExportService.php
```

---

## Decision Tree: Where Does This File Go?

When creating a new file, ask yourself:

### 1. What domain does this belong to?

- Is it related to Commissions? → `*/Commissions/`
- Is it related to Reports? → `*/Reports/`
- Is it related to Users? → `*/Users/`

### 2. What is its purpose?

- Is it for a specific controller? → `*/Controllers/{ControllerName}/`
- Is it for a specific service? → `*/Services/{ServiceName}/`
- Is it a shared utility? → `*/Shared/` or `*/Common/`

### 3. Will there be related files?

- If YES → Create a subdirectory now, even if it starts with one file
- If NO → Consider if you're thinking too narrowly

### 4. Can you explain the location?

- If you can't explain why a file belongs in its location, **find a better location**
- The path should be self-documenting

---

## Anti-Patterns to Avoid

### ❌ "I'll organize it later"

No you won't. Organize it now. It takes 10 seconds to create a subdirectory.

### ❌ "It's just one file"

One file becomes two. Two becomes ten. Start with structure.

### ❌ "The directory only has 3 files"

That's fine! A small, well-organized directory is better than a large flat one. Structure communicates intent regardless of file count.

### ❌ "I don't know where it goes, so I'll put it in the root"

If you don't know where it goes, **stop and think**. Ask yourself:
- What feature is this for?
- What other files is this related to?
- If I were looking for this file in 6 months, where would I look?

### ❌ Utility/Helper dumping grounds

```
❌ BAD - Catch-all directories
app/Helpers/
  StringHelper.php
  DateHelper.php
  CommissionHelper.php  ← This belongs in app/Services/Commissions/
  ReportHelper.php      ← This belongs in app/Services/Reports/
```

"Helper" and "Utility" directories often become dumping grounds. If a helper is domain-specific, put it in the domain directory.

---

## When to Create a New Subdirectory

Create a subdirectory when:

1. **You have 2+ related files** - Don't wait for 5-7, start organizing early
2. **The files share a common prefix** - `CommissionCalc*`, `Report*` → make a directory
3. **The files serve a single feature** - Group by feature, not by type
4. **You're creating a new domain concept** - New feature = new directory

**Inside a module, this rule organises files within the module's own folders. It never creates a sub-module.** A sub-module follows deep-modules' promotion criteria: a slice of the domain, never a page, screen, tab or view of it (see the deep-modules design principle, `promotion-criteria.md`).

---

## Refactoring Existing Flat Structures

When you encounter a flat directory with many files:

1. **Identify domains/features** - What logical groups exist?
2. **Create subdirectories** - One per domain/feature
3. **Move files** - Update namespaces/imports
4. **Update references** - Ensure nothing breaks

This is **always worth doing**. The short-term cost of refactoring is far less than the long-term cost of navigating chaos.

---

## Final Reminder

> **Speed of writing code is irrelevant. Maintainability is everything.**

The few seconds you "save" by dropping a file in a flat directory will cost hours of confusion later. Every. Single. Time.

**Think about structure. Think about the future. Put files where they belong.**

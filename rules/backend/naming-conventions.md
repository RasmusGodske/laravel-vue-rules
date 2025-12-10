---
paths: app/**/*.php
---

# Naming Conventions

## Data Classes and Request Classes

**Rule: Always use domain-specific, contextual names. Never use generic names.**

Data classes and request classes should answer:
1. **What domain?** (Product, Order, User, Invoice, Report)
2. **What action/purpose?** (Get, Create, Update, Calculate, Export)
3. **What specific data?** (Details, Configuration, Summary, Statistics)

### ❌ WRONG - Generic Names (Bad)

```php
// Too generic - doesn't indicate domain or specific purpose
GetContextRequestData.php
GetDataRequest.php
CalculateRequest.php
ReportData.php
ConfigData.php
ExportRequestData.php
DetailsData.php
```

**Why wrong:**
- Impossible to tell what this is for without opening the file
- Will conflict when you need multiple similar classes
- Not future-proof or extensible
- Forces you to rely on directory structure alone for context

### ✅ CORRECT - Domain-Specific Names (Good)

```php
// Clear domain, action, and purpose
ProductInventoryReportRequestData.php
OrderShippingCalculationRequestData.php
UserProfileSettingsData.php
InvoiceExportConfigurationData.php
CustomerPaymentDetailsData.php

// Alternative acceptable patterns:
GetProductInventoryReportRequestData.php
CalculateOrderShippingRequestData.php
ExportInvoiceConfigurationData.php
```

**Why correct:**
- Clear domain and purpose at a glance
- Room for related classes (ProductSalesReportRequestData.php, ProductStockReportRequestData.php)
- Self-documenting
- Future-proof

### When to Be More Specific

Ask yourself: **"Could there be another similar class in a different domain?"**

- If yes → Add domain prefix (Product, Order, User, Invoice, etc.)
- If unclear → Always add domain prefix to be safe

### Common Generic Names to Avoid

| ❌ Avoid | ✅ Use Instead |
|---------|---------------|
| `GetDataRequest` | `GetProductCatalogDataRequest` |
| `ConfigData` | `EmailNotificationConfigData` |
| `CalculateRequest` | `CalculateOrderTotalRequest` |
| `ReportData` | `SalesRevenueReportData` |
| `ExportRequest` | `ExportCustomerListRequest` |
| `DetailsData` | `UserAccountDetailsData` |
| `SettingsData` | `ApplicationThemeSettingsData` |

### Pattern to Follow

```
[Domain][Action/Purpose][Specifics]Data
```

Examples:
- `ProductCatalogExportRequestData`
- `OrderPaymentCalculationRequestData`
- `UserNotificationPreferencesData`
- `InvoicePdfGenerationConfigData`
- `CustomerRegistrationDetailsData`

### Real-World Scenarios

**Scenario 1: Building an E-commerce System**

You need request data for getting product information for reports.

❌ Bad: `GetReportRequestData.php`
✅ Good: `GetProductSalesReportRequestData.php`

Later you add invoice reports:
- `GetInvoiceSummaryReportRequestData.php` ← No naming conflict!

**Scenario 2: Configuration Data**

You need to store email settings.

❌ Bad: `ConfigData.php`
✅ Good: `EmailServiceConfigData.php`

Later you add SMS settings:
- `SmsServiceConfigData.php` ← Clear distinction!

**Scenario 3: Export Features**

You need to export customer lists.

❌ Bad: `ExportRequestData.php`
✅ Good: `ExportCustomerListRequestData.php`

Later you add product exports:
- `ExportProductCatalogRequestData.php` ← No confusion!

## Before Creating Data/Request Classes - Checklist

- [ ] Name includes domain context (Product, Order, User, Invoice)?
- [ ] Name is specific enough to avoid conflicts with future classes?
- [ ] Name clearly indicates purpose without opening file?
- [ ] Would a developer understand this name 6 months from now?
- [ ] Could this name conflict with a similar class in another domain?

## Summary

**Think long-term:** Your codebase will grow. Generic names cause naming conflicts and confusion. Always be specific about domain and purpose. If in doubt, add more specificity rather than less.

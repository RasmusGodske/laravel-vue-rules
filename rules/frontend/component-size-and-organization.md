---
paths: resources/js/**/*.vue
---

# Component Size & Organization

**Core Principle:** A component should do ONE thing well. When a component exceeds size limits, refactor BEFORE continuing.

## 🚨 Hard Limits - Stop and Refactor Immediately

| Metric | Warning | Hard Limit | Action |
|--------|---------|------------|--------|
| **Template Lines** | 100 | 150 | Extract sections to components |
| **Script Lines** | 150 | 200 | Extract composables or components |
| **Props Count** | 5 | 7 | Group props or split responsibilities |
| **State Variables** | 5 | 7 | Extract state to child components |
| **Nesting Depth** | 4 | 5 | Extract nested sections |
| **Emits Count** | 4 | 6 | Component has too many responsibilities |

**"But it's almost done!"** - No. Stop. Refactor first. Tech debt compounds quickly.

## 6 Red Flags Requiring Immediate Extraction

### 🚩 #1: Multiple Modals/Dialogs Inline

**Problem:** Component manages multiple modals with inline content.

```vue
<!-- ❌ BAD: 4 modals × 50 lines each = 200+ lines in one file -->
<script setup>
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showExportModal = ref(false);
// 150+ lines of modal logic mixed together
</script>

<template>
  <div>
    <Modal v-if="showEditModal"><!-- 60 lines --></Modal>
    <Modal v-if="showDeleteModal"><!-- 40 lines --></Modal>
    <Modal v-if="showExportModal"><!-- 50 lines --></Modal>
  </div>
</template>
```

**✅ SOLUTION: One component per modal**

```vue
<!-- Show.vue - Clean orchestrator -->
<script setup>
import EditModal from './components/EditModal.vue';
import DeleteModal from './components/DeleteModal.vue';
import ExportModal from './components/ExportModal.vue';
</script>

<template>
  <EditModal v-model:isOpen="showEdit" :item="item" @saved="handleSaved" />
  <DeleteModal v-model:isOpen="showDelete" :item="item" />
  <ExportModal v-model:isOpen="showExport" :item="item" />
</template>
```

### 🚩 #2: Multi-Section Forms

**Problem:** One component with 3+ distinct form sections (personal info, address, payment, etc).

**✅ SOLUTION:** Extract each section to its own component with `v-model` for data binding.

```vue
<!-- RegistrationForm.vue - Orchestrator only -->
<template>
  <form @submit.prevent="handleSubmit" class="space-y-8">
    <PersonalInfoSection v-model="personalInfo" />
    <AddressSection v-model="address" />
    <PaymentSection v-model="payment" />
  </form>
</template>
```

### 🚩 #3: Complex Conditional Rendering

**Problem:** Multiple `v-if/v-else-if` blocks with 40+ lines each.

**✅ SOLUTION:** Extract each state to a component or use dynamic components.

```vue
<!-- ❌ BAD: 5 states × 40 lines = 200 lines -->
<div v-if="status === 'draft'"><!-- 40 lines --></div>
<div v-else-if="status === 'pending'"><!-- 45 lines --></div>

<!-- ✅ GOOD: Component per state -->
<DraftStatus v-if="status === 'draft'" :order="order" />
<PendingStatus v-else-if="status === 'pending'" :order="order" />
```

### 🚩 #4: Data Fetching + Display + Actions

**Problem:** One component does everything: fetches data, displays it, AND handles all actions.

**✅ SOLUTION:** Split into container (data) + presentational (display) + action components.

```vue
<!-- UsersPage.vue - Container (data only) -->
<script setup>
const users = ref([]);
onMounted(async () => {
  users.value = await fetchUsers();
});
</script>

<template>
  <UsersList :users="users" @edit="editingUser = $event" />
  <UserEditModal v-if="editingUser" :user="editingUser" />
</template>

<!-- UsersList.vue - Presentational (display only) -->
<template>
  <UserCard
    v-for="user in users"
    :key="user.id"
    :user="user"
    @edit="$emit('edit', user)"
  />
</template>
```

### 🚩 #5: Repeated Template Blocks

**Problem:** Same HTML structure copy-pasted with different data.

**✅ SOLUTION:** Extract to reusable component with `v-for`.

```vue
<!-- ❌ BAD: Repeated card structure -->
<div class="card">Product 1</div>
<div class="card">Product 2</div>
<div class="card">Product 3</div>

<!-- ✅ GOOD: Reusable component -->
<ProductCard v-for="p in products" :key="p.id" :product="p" />
```

### 🚩 #6: Scrolling Fatigue

**Problem:** You scroll multiple screens to see the whole component.

**✅ RULE: If you can't see the entire component on one screen, it's too big.**

## Decision Tree

```
Component over 150 lines?
├─ YES → Extract NOW
└─ NO ↓

Multiple modals/dialogs?
├─ YES → Extract each modal
└─ NO ↓

Form with 3+ sections?
├─ YES → Extract each section
└─ NO ↓

Fetch + Display + Actions?
├─ YES → Split into container + presentational
└─ NO ↓

Repeated blocks?
├─ YES → Extract to reusable component
└─ NO → You're probably fine
```

## Directory Structure

Place page-specific components in a `components/` subdirectory:

```
pages/
  CommissionPlans/
    Show.vue                           # Orchestrator
    components/
      EditCommissionPlanModal.vue      # Edit modal
      DeleteCommissionPlanModal.vue    # Delete modal
      PauseUnpauseControls.vue         # Pause/unpause logic
      CommissionPlanHeader.vue         # Header section
```

## Naming Conventions

**Be specific, not generic:**

```
✅ GOOD                          ❌ BAD
CommissionPlanCard.vue           Card.vue
EditUserModal.vue                UserModal.vue
InvoiceLineItem.vue              Item.vue
OrderStatusBadge.vue             Badge.vue
```

## The Checklist

Before committing, verify:

- ✅ Template under 150 lines (target: 80)
- ✅ Script under 200 lines (target: 100)
- ✅ Single responsibility - does ONE thing
- ✅ Props ≤7 (target: ≤5)
- ✅ State variables ≤7 (target: ≤5)
- ✅ Each modal extracted to separate component
- ✅ Multi-section forms split into section components
- ✅ Nesting ≤5 levels (target: ≤4)

## The Golden Rule

**"If you scroll more than 2 screens to see the whole component, it's too big."**

Extract early and often. It's easier to combine small components than split large ones.

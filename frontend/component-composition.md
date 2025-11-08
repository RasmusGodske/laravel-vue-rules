# Component Composition & Single Responsibility

Guide for building maintainable Vue applications through small, focused components with clear responsibilities.

## Core Principle: Small, Focused Components

**CRITICAL:** Avoid creating large components that handle multiple responsibilities. Instead, break functionality into smaller, reusable components that each serve a single, clear purpose.

### Why Small Components Matter

- **Easier to understand** - Single responsibility is easier to grasp
- **Easier to test** - Isolated logic is simpler to test
- **Easier to maintain** - Changes are localized and predictable
- **Easier to reuse** - Small components can be used in multiple contexts
- **Easier to debug** - Fewer moving parts means fewer places to look
- **Better performance** - Vue can optimize smaller component trees more effectively

## Signs Your Component Is Too Large

Watch for these red flags:

### 🚨 Template Length
- **Too large:** More than ~100-150 lines in the `<template>`
- **Action:** Extract logical sections into child components

### 🚨 Multiple Responsibilities
- **Too large:** Component handles forms, data fetching, AND display logic
- **Action:** Split into separate components for each concern

### 🚨 Deeply Nested Template Structure
- **Too large:** Template has 5+ levels of nesting
- **Action:** Extract nested sections into components

### 🚨 Too Many Props
- **Too large:** More than 5-7 props
- **Action:** Consider grouping related props or splitting responsibilities

### 🚨 Too Many State Variables
- **Too large:** More than 5-7 `ref()` or `reactive()` declarations
- **Action:** Extract related state into child components or composables

### 🚨 Complex Script Section
- **Too large:** More than 150-200 lines in `<script>`
- **Action:** Extract logic into composables or child components

### 🚨 Repeating Template Patterns
- **Too large:** Similar HTML structures repeated multiple times
- **Action:** Extract the pattern into a reusable component

## Component Extraction Strategy

### Step 1: Identify Logical Boundaries

Look for distinct sections in your component:
- Form sections
- Lists or tables
- Cards or tiles
- Modals or dialogs
- Headers or footers
- Sidebar sections
- Data visualization
- Action buttons/toolbars

### Step 2: Extract to New Component

Create a new component file with a descriptive name:

```
✅ GOOD names:
- UserProfileForm.vue
- ProductCard.vue
- OrderStatusBadge.vue
- InvoiceActionButtons.vue
- CustomerSearchInput.vue

❌ BAD names:
- Form.vue (too generic)
- Component1.vue (meaningless)
- Stuff.vue (unclear purpose)
```

### Step 3: Define Clear Interface

Make the component's API explicit through props and events:

```vue
<script setup lang="ts">
import type { PropType } from 'vue';

interface Product {
  id: number;
  name: string;
  price: number;
  inStock: boolean;
}

// Clear, focused props
const props = defineProps({
  product: {
    type: Object as PropType<Product>,
    required: true,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
});

// Clear, focused events
const emit = defineEmits<{
  addToCart: [productId: number],
  viewDetails: [productId: number]
}>();
</script>
```

## Patterns for Good Component Composition

### Pattern 1: Container/Presentational Split

Separate data management from presentation:

```vue
<!-- ❌ BAD: Everything in one component -->
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { fetchUsers } from '@/api/users';

const users = ref([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  users.value = await fetchUsers();
  loading.value = false;
});
</script>

<template>
  <div v-if="loading">Loading...</div>
  <div v-else>
    <div v-for="user in users" :key="user.id" class="user-card">
      <img :src="user.avatar" class="avatar" />
      <div class="user-info">
        <h3>{{ user.name }}</h3>
        <p>{{ user.email }}</p>
        <span class="badge">{{ user.status }}</span>
      </div>
    </div>
  </div>
</template>
```

```vue
<!-- ✅ GOOD: Container component (data management) -->
<!-- UserListContainer.vue -->
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { fetchUsers } from '@/api/users';
import UserCard from './UserCard.vue';

const users = ref([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  users.value = await fetchUsers();
  loading.value = false;
});
</script>

<template>
  <div v-if="loading">Loading...</div>
  <div v-else class="grid gap-4">
    <UserCard
      v-for="user in users"
      :key="user.id"
      :user="user"
    />
  </div>
</template>

<!-- ✅ GOOD: Presentational component (display only) -->
<!-- UserCard.vue -->
<script setup lang="ts">
import type { PropType } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
  avatar: string;
  status: string;
}

const props = defineProps({
  user: {
    type: Object as PropType<User>,
    required: true,
  },
});
</script>

<template>
  <div class="flex items-center gap-4 p-4 bg-white rounded-lg shadow">
    <img :src="user.avatar" class="w-12 h-12 rounded-full" />
    <div class="flex-1">
      <h3 class="font-semibold text-gray-900">{{ user.name }}</h3>
      <p class="text-sm text-gray-600">{{ user.email }}</p>
    </div>
    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
      {{ user.status }}
    </span>
  </div>
</template>
```

### Pattern 2: Extract Form Sections

Break large forms into focused sections:

```vue
<!-- ❌ BAD: Monolithic form component -->
<template>
  <form>
    <!-- 50+ lines of personal info fields -->
    <!-- 30+ lines of address fields -->
    <!-- 40+ lines of payment fields -->
    <!-- 20+ lines of preferences -->
  </form>
</template>

<!-- ✅ GOOD: Composed form with sections -->
<!-- UserRegistrationForm.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import PersonalInfoSection from './PersonalInfoSection.vue';
import AddressSection from './AddressSection.vue';
import PaymentSection from './PaymentSection.vue';
import PreferencesSection from './PreferencesSection.vue';

const personalInfo = ref({});
const address = ref({});
const payment = ref({});
const preferences = ref({});
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-8">
    <PersonalInfoSection v-model="personalInfo" />
    <AddressSection v-model="address" />
    <PaymentSection v-model="payment" />
    <PreferencesSection v-model="preferences" />

    <div class="flex justify-end gap-3">
      <button type="button" @click="handleCancel">Cancel</button>
      <button type="submit">Register</button>
    </div>
  </form>
</template>
```

### Pattern 3: Extract Repeated UI Elements

Identify patterns and create reusable components:

```vue
<!-- ❌ BAD: Repeated badge logic -->
<template>
  <div>
    <span
      class="px-2 py-1 text-xs rounded"
      :class="{
        'bg-green-100 text-green-800': order.status === 'completed',
        'bg-yellow-100 text-yellow-800': order.status === 'pending',
        'bg-red-100 text-red-800': order.status === 'failed',
      }"
    >
      {{ order.status }}
    </span>

    <!-- Same badge logic repeated elsewhere -->
    <span
      class="px-2 py-1 text-xs rounded"
      :class="{
        'bg-green-100 text-green-800': shipment.status === 'completed',
        'bg-yellow-100 text-yellow-800': shipment.status === 'pending',
        'bg-red-100 text-red-800': shipment.status === 'failed',
      }"
    >
      {{ shipment.status }}
    </span>
  </div>
</template>

<!-- ✅ GOOD: Reusable badge component -->
<!-- StatusBadge.vue -->
<script setup lang="ts">
const props = defineProps({
  status: {
    type: String as PropType<'completed' | 'pending' | 'failed'>,
    required: true,
  },
});

const colorClasses = computed(() => {
  const colors = {
    completed: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800',
  };
  return colors[props.status];
});
</script>

<template>
  <span class="px-2 py-1 text-xs font-medium rounded" :class="colorClasses">
    {{ status }}
  </span>
</template>

<!-- Usage -->
<template>
  <div>
    <StatusBadge :status="order.status" />
    <StatusBadge :status="shipment.status" />
  </div>
</template>
```

### Pattern 4: Extract Modal/Dialog Content

Keep modal logic separate from content:

```vue
<!-- ❌ BAD: Modal logic mixed with content -->
<script setup lang="ts">
const isOpen = ref(false);
const form = ref({ name: '', email: '' });
// 100+ lines of form logic
</script>

<template>
  <Dialog v-model:isOpen="isOpen">
    <!-- 150+ lines of form content -->
  </Dialog>
</template>

<!-- ✅ GOOD: Separate modal and content -->
<!-- UserEditModal.vue -->
<script setup lang="ts">
import Dialog from '@/components/Dialog.vue';
import UserEditForm from './UserEditForm.vue';

const isOpen = defineModel<boolean>('isOpen', { default: false });
</script>

<template>
  <Dialog v-model:isOpen="isOpen" title="Edit User">
    <UserEditForm @saved="isOpen = false" @cancelled="isOpen = false" />
  </Dialog>
</template>

<!-- UserEditForm.vue (separate, reusable) -->
<script setup lang="ts">
const form = ref({ name: '', email: '' });
const emit = defineEmits<{
  saved: [],
  cancelled: []
}>();
// Focused form logic only
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <!-- Form content -->
  </form>
</template>
```

### Pattern 5: Extract Action Buttons/Toolbars

Group related actions into components:

```vue
<!-- ❌ BAD: Actions scattered in parent -->
<template>
  <div>
    <div class="header">
      <button @click="handleEdit">Edit</button>
      <button @click="handleDelete">Delete</button>
      <button @click="handleDuplicate">Duplicate</button>
      <button @click="handleExport">Export</button>
      <button @click="handleShare">Share</button>
    </div>
    <!-- Rest of component -->
  </div>
</template>

<!-- ✅ GOOD: Extracted action toolbar -->
<!-- OrderActionButtons.vue -->
<script setup lang="ts">
const emit = defineEmits<{
  edit: [],
  delete: [],
  duplicate: [],
  export: [],
  share: []
}>();
</script>

<template>
  <div class="flex items-center gap-2">
    <button @click="emit('edit')" class="btn btn-primary">
      Edit
    </button>
    <button @click="emit('delete')" class="btn btn-danger">
      Delete
    </button>
    <button @click="emit('duplicate')" class="btn btn-secondary">
      Duplicate
    </button>
    <button @click="emit('export')" class="btn btn-secondary">
      Export
    </button>
    <button @click="emit('share')" class="btn btn-secondary">
      Share
    </button>
  </div>
</template>

<!-- Usage in parent -->
<template>
  <div>
    <OrderActionButtons
      @edit="handleEdit"
      @delete="handleDelete"
      @duplicate="handleDuplicate"
      @export="handleExport"
      @share="handleShare"
    />
    <!-- Rest of component -->
  </div>
</template>
```

## Guidelines for Component Size

### Ideal Component Size

**Template:**
- Target: 30-80 lines
- Maximum: ~150 lines
- Beyond 150 lines: MUST extract components

**Script:**
- Target: 50-100 lines
- Maximum: ~200 lines
- Beyond 200 lines: Extract composables or child components

**Number of Concerns:**
- Target: 1 primary responsibility
- Maximum: 2 closely related responsibilities
- More than 2: Split into multiple components

### When NOT to Extract

Don't over-extract. Keep components together when:

1. **Tightly coupled logic** - Logic that always works together and makes no sense separately
2. **Very small components** - Extracting a 5-line template adds unnecessary complexity
3. **One-off use** - Component is only ever used once and extraction adds no clarity
4. **Loss of context** - Splitting would make the code harder to understand, not easier

## Component Organization in Filesystem

### Organizing Related Components

```
pages/
  Orders/
    Index.vue                   # Main page
    Show.vue                    # Detail page
    components/                 # Page-specific components
      OrderCard.vue
      OrderFilters.vue
      OrderStatusBadge.vue
      OrderActionButtons.vue

components/
  ui/                          # Shared UI components
    Button.vue
    Input.vue
    Modal.vue
    Badge.vue
```

### Naming Conventions

```
✅ GOOD: Descriptive, specific names
- UserProfileCard.vue
- InvoiceLineItem.vue
- ProductFilterSidebar.vue
- OrderStatusBadge.vue
- ShippingAddressForm.vue

❌ BAD: Generic, vague names
- Card.vue
- Item.vue
- Sidebar.vue
- Badge.vue
- Form.vue
```

## Real-World Example: Before & After

### ❌ BEFORE: Monolithic Component (300+ lines)

```vue
<!-- OrderDetailsPage.vue - TOO LARGE! -->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

// Props
const props = defineProps({ orderId: Number });

// State (too much!)
const order = ref(null);
const loading = ref(false);
const editing = ref(false);
const form = ref({});
const items = ref([]);
const customer = ref(null);
const shippingAddress = ref({});
const billingAddress = ref({});
const notes = ref('');
const statusHistory = ref([]);
const showDeleteModal = ref(false);
const showRefundModal = ref(false);

// 200+ more lines of mixed logic for:
// - Data fetching
// - Form handling
// - Calculations
// - Status updates
// - Modals
// - Validation
</script>

<template>
  <!-- 200+ lines of template mixing: -->
  <!-- - Order header -->
  <!-- - Customer info -->
  <!-- - Item list -->
  <!-- - Shipping/billing addresses -->
  <!-- - Status history -->
  <!-- - Action buttons -->
  <!-- - Multiple modals -->
  <!-- - Forms -->
</template>
```

### ✅ AFTER: Composed Components (~50 lines each)

```vue
<!-- OrderDetailsPage.vue - Container component -->
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { fetchOrder } from '@/api/orders';
import OrderHeader from './components/OrderHeader.vue';
import OrderCustomerInfo from './components/OrderCustomerInfo.vue';
import OrderItemsList from './components/OrderItemsList.vue';
import OrderAddresses from './components/OrderAddresses.vue';
import OrderStatusHistory from './components/OrderStatusHistory.vue';
import OrderActions from './components/OrderActions.vue';

const props = defineProps({
  orderId: {
    type: Number,
    required: true,
  },
});

const order = ref(null);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  order.value = await fetchOrder(props.orderId);
  loading.value = false;
});

function handleOrderUpdated(updatedOrder) {
  order.value = updatedOrder;
}
</script>

<template>
  <div v-if="loading">Loading...</div>
  <div v-else-if="order" class="space-y-6">
    <OrderHeader :order="order" />
    <OrderCustomerInfo :customer="order.customer" />
    <OrderItemsList :items="order.items" />
    <OrderAddresses
      :shipping="order.shippingAddress"
      :billing="order.billingAddress"
    />
    <OrderStatusHistory :history="order.statusHistory" />
    <OrderActions
      :order="order"
      @updated="handleOrderUpdated"
    />
  </div>
</template>

<!-- Each child component is 30-80 lines and handles ONE concern -->
<!-- OrderHeader.vue - Display order number, date, total -->
<!-- OrderCustomerInfo.vue - Display customer details -->
<!-- OrderItemsList.vue - Display and manage order items -->
<!-- OrderAddresses.vue - Display shipping/billing addresses -->
<!-- OrderStatusHistory.vue - Display status timeline -->
<!-- OrderActions.vue - Edit, delete, refund buttons and modals -->
```

## Checklist: Before Committing Component Code

Ask yourself:

- ✅ Is my component focused on ONE primary responsibility?
- ✅ Is my template under 150 lines?
- ✅ Is my script under 200 lines?
- ✅ Could any section be extracted into a reusable component?
- ✅ Are there repeated patterns that should be components?
- ✅ Would extracting improve clarity and maintainability?
- ✅ Do my component names clearly describe their purpose?
- ✅ Can someone understand this component in under 2 minutes?

## Final Principle

**When in doubt, extract it out.**

It's easier to start with smaller components and combine them than to split a large component later. Err on the side of creating more, smaller components.

**If you're writing a component over 150 lines, STOP and ask:**
- "What can I extract?"
- "What are the distinct responsibilities?"
- "Which sections could be reusable?"

Then refactor before continuing.

# Vue.js Component Development Conventions

Guide for creating modern, maintainable Vue 3 components using Composition API, TypeScript, and best practices.

## 🚨 CRITICAL: Use defineModel(), NOT modelValue!

**This is the #1 mistake to avoid!** Use Vue 3.4+ `defineModel()` for two-way binding, not the old `modelValue` pattern.

### ❌ WRONG - Old Pattern

```vue
<script setup>
// BAD - Using modelValue prop + manual emit
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (value) => {
  emit('update:modelValue', value);
};
</script>
```

### ✅ CORRECT - Modern Pattern

```vue
<script setup lang="ts">
// GOOD - Using defineModel()
const model = defineModel<string>({ default: '' });
</script>

<template>
  <input v-model="model" />
</template>
```

**Why defineModel() is better:**
- Cleaner, less boilerplate
- No manual emit or watch logic
- Type-safe with TypeScript
- Automatic two-way binding
- Official Vue 3.4+ pattern

## Component Structure

**STRICT ORDER** - Always follow this structure:

```vue
<script setup lang="ts">
// 1. Imports
import { ref, computed } from 'vue';
import Button from '@/components/Button.vue';

// 2. Props
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
});

// 3. Models (for two-way binding)
const isOpen = defineModel<boolean>('isOpen', { default: false });

// 4. Emits
const emit = defineEmits<{
  save: [data: string],
  cancel: []
}>();

// 5. Reactive state
const loading = ref(false);

// 6. Computed properties
const canSave = computed(() => !loading.value);

// 7. Methods
function handleSave() {
  emit('save', 'data');
}
</script>

<template>
  <!-- HTML here -->
</template>

<style scoped>
/* Scoped styles here */
</style>
```

**Key Requirements:**
- ✅ `<script setup lang="ts">` for TypeScript (REQUIRED for new components)
- ✅ Script at top, template middle, style at bottom
- ✅ Use `<style scoped>` to prevent style leakage
- ✅ Follow the 7-step structure order inside script

## Two-Way Binding with defineModel()

### Basic Usage

```vue
<script setup lang="ts">
// Simple model
const model = defineModel<string>();

// With default value
const checked = defineModel<boolean>({ default: false });

// With required
const count = defineModel<number>({ required: true });
</script>

<template>
  <input v-model="model" />
  <input type="checkbox" v-model="checked" />
</template>
```

### Multiple Models

```vue
<script setup lang="ts">
// Named models for multiple v-model bindings
const firstName = defineModel<string>('firstName');
const lastName = defineModel<string>('lastName');
</script>

<template>
  <input v-model="firstName" placeholder="First name" />
  <input v-model="lastName" placeholder="Last name" />
</template>
```

### Usage in Parent Component

```vue
<template>
  <!-- Single model -->
  <CustomInput v-model="userName" />

  <!-- Multiple models -->
  <UserForm
    v-model:firstName="user.firstName"
    v-model:lastName="user.lastName"
  />
</template>
```

## Props Definition

**Always use full object syntax** with type, required, and default:

```vue
<script setup lang="ts">
import type { PropType } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
}

const props = defineProps({
  // Simple types
  title: {
    type: String,
    required: true,
  },

  // With default
  isActive: {
    type: Boolean,
    default: false,
  },

  // Multiple types
  value: {
    type: [String, Number],
    default: '',
  },

  // Complex types with PropType
  user: {
    type: Object as PropType<User>,
    required: true,
  },

  // Array of specific type
  items: {
    type: Array as PropType<string[]>,
    default: () => [],
  },

  // Object with specific shape
  config: {
    type: Object as PropType<{ enabled: boolean; count: number }>,
    default: () => ({ enabled: true, count: 0 }),
  },
});
</script>
```

## TypeScript Support

### Always Use TypeScript for New Components

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';
import type { PropType, ComputedRef } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
}

const props = defineProps({
  user: {
    type: Object as PropType<User>,
    required: true,
  },
});

const model = defineModel<string | null>({ default: null });
const count = ref<number>(0);
const users = ref<User[]>([]);

const formattedName = computed<string>(() => {
  return props.user.name.toUpperCase();
});

function updateCount(value: number): void {
  count.value = value;
}
</script>
```

### Type Imports

```typescript
// Vue types
import type { PropType, ComputedRef, Ref } from 'vue';

// Your app types
import type { User, Product, Order } from '@/types';
```

## Component Communication

### Props Down, Events Up

```vue
<!-- Parent.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import ChildComponent from './ChildComponent.vue';

const selectedItem = ref<string | null>(null);

function handleSelection(item: string) {
  selectedItem.value = item;
}
</script>

<template>
  <ChildComponent
    :items="['A', 'B', 'C']"
    @item-selected="handleSelection"
  />
</template>

<!-- ChildComponent.vue -->
<script setup lang="ts">
const props = defineProps({
  items: {
    type: Array as PropType<string[]>,
    required: true,
  },
});

const emit = defineEmits<{
  itemSelected: [item: string]
}>();

function selectItem(item: string) {
  emit('itemSelected', item);
}
</script>

<template>
  <ul>
    <li
      v-for="item in items"
      :key="item"
      @click="selectItem(item)"
    >
      {{ item }}
    </li>
  </ul>
</template>
```

## Styling with Scoped CSS

### Use Scoped Styles

```vue
<template>
  <div class="container">
    <button class="btn-primary">Click me</button>
  </div>
</template>

<style scoped>
/* Scoped to this component only */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.btn-primary {
  background: blue;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
}

/* Deep selector for child components */
:deep(.child-class) {
  color: gray;
}
</style>
```

### Using Tailwind CSS

```vue
<template>
  <!-- Group related classes logically -->
  <div class="flex items-center justify-between gap-4 p-4 bg-white rounded-lg shadow-md">
    <span class="text-lg font-bold text-gray-900">Title</span>
    <button class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">
      Action
    </button>
  </div>
</template>
```

## Early Exit Pattern

Reduce nesting with early returns:

```vue
<script setup lang="ts">
// ✅ GOOD - Early exit
function processUser(user: User | null) {
  if (!user) {
    return;
  }

  if (!user.isActive) {
    return;
  }

  // Main logic here
  updateUserData(user);
}

// ❌ BAD - Deep nesting
function processUser(user: User | null) {
  if (user) {
    if (user.isActive) {
      // Main logic deeply nested
      updateUserData(user);
    }
  }
}
</script>
```

## Composables

Extract reusable logic into composables:

```typescript
// composables/useCounter.ts
import { ref, computed } from 'vue';

export function useCounter(initialValue = 0) {
  const count = ref(initialValue);
  const doubled = computed(() => count.value * 2);

  function increment() {
    count.value++;
  }

  function decrement() {
    count.value--;
  }

  function reset() {
    count.value = initialValue;
  }

  return {
    count,
    doubled,
    increment,
    decrement,
    reset,
  };
}
```

```vue
<!-- Usage in component -->
<script setup lang="ts">
import { useCounter } from '@/composables/useCounter';

const { count, doubled, increment, decrement, reset } = useCounter(10);
</script>

<template>
  <div>
    <p>Count: {{ count }}</p>
    <p>Doubled: {{ doubled }}</p>
    <button @click="increment">+</button>
    <button @click="decrement">-</button>
    <button @click="reset">Reset</button>
  </div>
</template>
```

## Complete Example: Modern Component

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';
import type { PropType } from 'vue';
import Button from '@/components/Button.vue';
import Input from '@/components/Input.vue';

interface User {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive';
}

// Props
const props = defineProps({
  user: {
    type: Object as PropType<User>,
    required: true,
  },
  isEditable: {
    type: Boolean,
    default: false,
  },
});

// Models
const isModalOpen = defineModel<boolean>('isModalOpen', { default: false });

// Emits
const emit = defineEmits<{
  saved: [user: User],
  cancelled: []
}>();

// State
const form = ref({
  name: props.user.name,
  email: props.user.email,
});
const loading = ref(false);

// Computed
const isActive = computed(() => props.user.status === 'active');

const canSubmit = computed(() => {
  return form.value.name.length > 0 &&
         form.value.email.length > 0 &&
         !loading.value;
});

// Methods
async function handleSubmit(): Promise<void> {
  if (!canSubmit.value) return;

  loading.value = true;
  try {
    // API call here
    await saveUser(form.value);
    isModalOpen.value = false;
    emit('saved', { ...props.user, ...form.value });
  } catch (error) {
    console.error('Failed to save user', error);
  } finally {
    loading.value = false;
  }
}

function handleCancel(): void {
  form.value = {
    name: props.user.name,
    email: props.user.email,
  };
  isModalOpen.value = false;
  emit('cancelled');
}
</script>

<template>
  <div class="max-w-2xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">
        Edit User
      </h2>
      <span
        class="px-3 py-1 text-sm font-medium rounded-full"
        :class="{
          'bg-green-100 text-green-800': isActive,
          'bg-gray-100 text-gray-800': !isActive,
        }"
      >
        {{ isActive ? 'Active' : 'Inactive' }}
      </span>
    </div>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Name Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Name
        </label>
        <Input
          v-model="form.name"
          type="text"
          :disabled="!isEditable"
        />
      </div>

      <!-- Email Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Email
        </label>
        <Input
          v-model="form.email"
          type="email"
          :disabled="!isEditable"
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-4">
        <Button
          type="button"
          variant="secondary"
          @click="handleCancel"
        >
          Cancel
        </Button>
        <Button
          type="submit"
          variant="primary"
          :disabled="!canSubmit"
          :loading="loading"
        >
          Save Changes
        </Button>
      </div>
    </form>
  </div>
</template>

<style scoped>
/* Component-specific styles if needed */
</style>
```

## Checklist for New Components

Before considering a component complete:

- ✅ Uses `<script setup lang="ts">` with TypeScript
- ✅ Uses `defineModel()` for two-way binding (NOT modelValue prop)
- ✅ Follows script → template → style order
- ✅ Props use full object syntax with PropType for complex types
- ✅ Has `<style scoped>` if styles are needed
- ✅ Uses early exit patterns to reduce nesting
- ✅ Emits are properly typed (TypeScript)
- ✅ Component communicates via props down, events up
- ✅ Extracts reusable logic into composables when appropriate

## Anti-Patterns to Avoid

### ❌ Don't Do This

```vue
<!-- 1. Using modelValue prop instead of defineModel -->
<script setup>
const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
</script>

<!-- 2. Missing TypeScript -->
<script setup>  <!-- No lang="ts" -->
const props = defineProps({
  user: Object,  // No PropType
});
</script>

<!-- 3. No scoped styles -->
<style>  <!-- Not scoped! -->
.my-class { }
</style>

<!-- 4. Properties outside constructor in Data classes -->
<script setup>
const data = {
  count: 0  // Should use ref()
};
</script>
```

### ✅ Do This Instead

```vue
<!-- 1. Use defineModel -->
<script setup lang="ts">
const model = defineModel<string>();
</script>

<!-- 2. Include TypeScript -->
<script setup lang="ts">
import type { PropType } from 'vue';
const props = defineProps({
  user: {
    type: Object as PropType<User>,
    required: true,
  },
});
</script>

<!-- 3. Use scoped styles -->
<style scoped>
.my-class { }
</style>

<!-- 4. Use ref() for reactive data -->
<script setup lang="ts">
const count = ref(0);
</script>
```

## Final Reminder

**The #1 mistake to avoid:** Using `modelValue` prop pattern instead of `defineModel()`.

If you see this in new code:
```vue
const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
```

**STOP** and use this instead:
```vue
const model = defineModel<YourType>();
```

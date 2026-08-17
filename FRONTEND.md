# Frontend Architecture

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Vue 3 (Composition API) |
| Router | Inertia.js v2 (no client-side routing) |
| Styling | Tailwind CSS v3 |
| Icons | Heroicons |
| Charts | Chart.js |
| Calendar | FullCalendar |
| Build Tool | Vite |

## Directory Structure

```
resources/js/
├── app.js                  # Entry point
├── app.css                 # Global styles
├── Pages/                  # Page components (Inertia pages)
│   ├── Hive/               # Protected admin/staff pages
│   │   ├── Dashboard.vue
│   │   ├── Students/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Show.vue
│   │   │   └── Edit.vue
│   │   ├── Staff/
│   │   ├── Finance/
│   │   │   ├── Invoice/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Show.vue
│   │   │   │   └── Create.vue
│   │   │   ├── Payments/
│   │   │   └── Expenses/
│   │   ├── Academic/
│   │   │   ├── Modules/
│   │   │   ├── Cohorts/
│   │   │   └── Programmes/
│   │   ├── Library/
│   │   │   ├── Books/
│   │   │   └── Reservations/
│   │   ├── Events/
│   │   ├── Documents/
│   │   └── Settings/
│   └── Public/             # Public pages
│       ├── Home.vue
│       ├── Apply.vue
│       └── Login.vue
├── Components/             # Reusable components
│   ├── Pagination.vue
│   ├── Modal.vue
│   ├── DataTable.vue
│   ├── FileUpload.vue
│   ├── StatusBadge.vue
│   └── EmptyState.vue
├── Layouts/                # Page layouts
│   ├── HiveLayout.vue
│   ├── GuestLayout.vue
│   └── PublicLayout.vue
├── Composables/            # Reusable composition functions
│   ├── useUser.js
│   ├── useFilters.js
│   ├── usePermissions.js
│   └── useForm.js
└── lib/                    # Third-party integrations
    ├── axios.js
    └── inertia.js
```

## Page Components

Pages are Inertia.js components that map 1:1 to Laravel controller actions.

### Page Structure

```vue
<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    students: Object,
    filters: Object,
    can: Object,
})

const search = ref(props.filters.search ?? '')
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold">Students</h1>
        
        <!-- Search -->
        <input v-model="search" type="text" placeholder="Search...">
        
        <!-- Data Table -->
        <div class="mt-6">
            <table>
                <tr v-for="student in students.data" :key="student.id">
                    <td>{{ student.first_name }} {{ student.last_name }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Pagination -->
        <Pagination :links="students.links" />
    </div>
</template>
```

## Layouts

### HiveLayout (Protected)

```vue
<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Sidebar from '@/Components/Sidebar.vue'
import TopNav from '@/Components/TopNav.vue'

defineOptions({
    layout: AppLayout,
})

const page = usePage()
const user = computed(() => page.props.auth.user)
const can = computed(() => page.props.auth.permissions)
</script>

<template>
    <div class="flex h-screen">
        <Sidebar :user="user" :permissions="can" />
        <div class="flex-1 flex flex-col">
            <TopNav :user="user" />
            <main class="flex-1 overflow-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
```

## Composables

### usePermissions

```js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePermissions() {
    const page = usePage()
    const permissions = computed(() => page.props.auth.permissions ?? [])
    const roles = computed(() => page.props.auth.roles ?? [])

    const can = (permission) => {
        return permissions.value.includes(permission)
    }

    const hasRole = (role) => {
        return roles.value.includes(role)
    }

    const isAdmin = () => {
        return ['super-admin', 'it-support'].some(r => roles.value.includes(r))
    }

    return {
        permissions,
        roles,
        can,
        hasRole,
        isAdmin,
    }
}
```

### useForm

```js
import { useForm } from '@inertiajs/vue3'

export function useInvoiceForm(initialData = {}) {
    return useForm({
        invoice_number: initialData.invoice_number ?? '',
        student_id: initialData.student_id ?? '',
        amount: initialData.amount ?? '',
        due_date: initialData.due_date ?? '',
        type: initialData.type ?? 'tuition',
        status: initialData.status ?? 'pending',
        notes: initialData.notes ?? '',
    })
}
```

## Inertia Patterns

### Form Submission

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
})

const submit = () => {
    form.post('/hive/students', {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <form @submit.prevent="submit">
        <input v-model="form.first_name" type="text">
        <div v-if="form.errors.first_name" class="error">{{ form.errors.first_name }}</div>
        
        <button :disabled="form.processing">Save</button>
    </form>
</template>
```

### Delete Confirmation

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ student: Object })

const form = useForm({})

const destroy = () => {
    if (confirm('Are you sure?')) {
        form.delete(`/hive/students/${props.student.id}`)
    }
}
</script>
```

### Filtering

```vue
<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ filters: Object })

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

watch([search, status], () => {
    router.get('/hive/invoices', 
        { search: search.value, status: status.value },
        { preserveState: true, replace: true }
    )
})
</script>
```

## Components

### Pagination Component

```vue
<!-- resources/js/Components/Pagination.vue -->
<script setup>
defineProps({
    links: Object,
})
</script>

<template>
    <nav v-if="links.links.length > 3">
        <ul class="flex gap-1">
            <li v-for="link in links.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    :class="{ 'font-bold': link.active }"
                    v-html="link.label"
                />
                <span v-else v-html="link.label" class="text-gray-400" />
            </li>
        </ul>
    </nav>
</template>
```

### Modal Component

```vue
<!-- resources/js/Components/Modal.vue -->
<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    show: Boolean,
    maxWidth: { type: String, default: '2xl' },
})

const emit = defineEmits(['close'])
const close = () => emit('close')

const closeOnEscape = (e) => {
    if (e.key === 'Escape') close()
}

watch(() => props.show, (show) => {
    if (show) document.addEventListener('keydown', closeOnEscape)
    else document.removeEventListener('keydown', closeOnEscape)
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-black/50" @click="close" />
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
```

## Shared Data (Inertia Middleware)

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Inertia $inertia): array
{
    return [
        'auth' => [
            'user' => $this->auth->user(),
            'permissions' => $this->auth->user()?->getAllPermissions()->pluck('name') ?? [],
            'roles' => $this->auth->user()?->getRoleNames() ?? [],
        ],
        'flash' => [
            'success' => session('success'),
            'error' => session('error'),
        ],
        'reference' => app(ReferenceDataService::class)->all(),
    ];
}
```

## Vite Configuration

```js
// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
})
```

## Building for Production

```bash
# Build assets
npm run build

# Or with Vite directly
npx vite build
```

Output goes to `public/build/`.

## Styling Conventions

### Tailwind Classes Order

1. Layout (flex, grid, block)
2. Spacing (p-, m-, gap-)
3. Sizing (w-, h-, max-w-)
4. Typography (text-, font-)
5. Colors (bg-, text-, border-)
6. Effects (shadow-, rounded-, opacity-)
7. States (hover:, focus:, dark:)
8. Responsive (sm:, md:, lg:, xl:)

```vue
<!-- Good -->
<div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md">

<!-- Bad -->
<div class="rounded-lg shadow p-4 flex hover:shadow-md bg-white">
```

### Dark Mode

```vue
<!-- Use dark: prefix -->
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
```

## State Management

### No Vuex/Pinia

The project uses Inertia.js for server-side state management. Client-side state is minimal:

```js
// Use reactive refs for UI state
const isOpen = ref(false)
const activeTab = ref('overview')

// Use Inertia's usePage for server data
import { usePage } from '@inertiajs/vue3'
const user = computed(() => usePage().props.auth.user)
```

## Error Handling

```vue
<script setup>
import { onError } from '@inertiajs/vue3'

onError((error) => {
    if (error.response?.status === 422) {
        // Validation error
        console.error('Validation failed:', error.response.data.errors)
    }
})
</script>
```

## Best Practices

1. **No client-side routing** — All routes go through Inertia
2. **Server-side validation** — Validate in FormRequests, not client-side
3. **Minimal client state** — Use Inertia props, not local state
4. **Composition API** — Use `<script setup>` syntax
5. **Type safety** — Use JSDoc or TypeScript where helpful
6. **Accessible forms** — Use labels, ARIA attributes, error messages
7. **Progressive enhancement** — Forms should work without JavaScript
8. **Performance** — Lazy load components, optimize images

<template>
  <HiveLayout title="Roles &amp; Permissions" description="Control what each role can do across the system">
    <div class="space-y-6">
      <!-- Role selector -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Roles</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Select a role to edit the permissions it grants. Changes apply to every user holding that role.
          </p>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <button
            v-for="role in roles"
            :key="role.id"
            type="button"
            @click="selected = role"
            class="text-left p-4 rounded-lg border transition-colors"
            :class="roleCardClass(role)"
          >
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="font-medium text-gray-800 dark:text-white">{{ role.display_name }}</p>
                <p class="text-xs font-mono text-gray-400">{{ role.name }}</p>
              </div>
              <LockClosedIcon
                v-if="role.is_locked"
                class="h-4 w-4 text-gray-400 flex-shrink-0"
                title="This role cannot be edited"
              />
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              {{ role.permission_count }} permission{{ role.permission_count === 1 ? '' : 's' }}
              · {{ role.user_count }} user{{ role.user_count === 1 ? '' : 's' }}
            </p>
          </button>
        </div>
      </div>

      <!-- Permission matrix -->
      <form v-if="selected" @submit.prevent="submit" class="space-y-4">
        <div
          v-if="selected.is_locked"
          class="flex items-start gap-3 p-4 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
        >
          <LockClosedIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <p class="text-sm">
            The <strong>{{ selected.display_name }}</strong> role always holds every permission. It cannot be
            edited, so the system can never be locked out of role management.
          </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                {{ selected.display_name }} permissions
              </h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ selectedPermissions.length }} of {{ allPermissions.length }} selected
              </p>
            </div>
            <div class="flex items-center gap-2">
              <input
                v-model="filter"
                type="search"
                placeholder="Filter permissions"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
            </div>
          </div>

          <div class="p-6 space-y-6">
            <div v-for="(permissions, group) in filteredGroups" :key="group">
              <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">
                {{ group }}
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <label
                  v-for="permission in permissions"
                  :key="permission"
                  class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                >
                  <input
                    v-model="selectedPermissions"
                    :value="permission"
                    type="checkbox"
                    :disabled="selected.is_locked"
                    class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                  <span class="text-sm font-mono text-gray-700 dark:text-gray-200 break-all">{{ permission }}</span>
                </label>
              </div>
            </div>

            <p v-if="Object.keys(filteredGroups).length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
              No permissions match "{{ filter }}".
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3">
          <p v-if="isDirty" class="text-sm text-amber-700 dark:text-amber-400 mr-auto">
            You have unsaved changes.
          </p>
          <button
            v-if="!selected.is_locked"
            type="button"
            @click="selectAllFiltered"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"
          >
            {{ allFilteredSelected ? 'Clear filtered' : 'Select filtered' }}
          </button>
          <button
            type="button"
            @click="resetPermissions"
            :disabled="!isDirty"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"
          >
            Reset
          </button>
          <button
            type="submit"
            :disabled="selected.is_locked || !isDirty || form.processing"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50"
          >
            <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Save permissions
          </button>
        </div>
      </form>

      <EmptyState
        v-else
        type="users"
        title="No roles found"
        description="Run the RolePermissionSeeder to register the roles this installation uses."
      />
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { LockClosedIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    permissionGroups: {
        type: Object,
        default: () => ({}),
    },
});

const selected = ref(props.roles[0] ?? null);
const filter = ref('');

const allPermissions = computed(() => Object.values(props.permissionGroups).flat());

const SELECTED_CARD = 'border-amber-500 bg-amber-50 dark:bg-amber-900/20';
const UNSELECTED_CARD = 'border-gray-200 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-700';

const roleCardClass = (role) => (selected.value?.id === role.id ? SELECTED_CARD : UNSELECTED_CARD);

// The form is the single source of truth for the checkbox set, so there is no
// second array to keep in sync.
const form = useForm({ permissions: [...(selected.value?.permissions ?? [])] });

const selectedPermissions = computed({
    get: () => form.permissions,
    set: (value) => {
        form.permissions = value;
    },
});

const isDirty = computed(() => {
    if (!selected.value) return false;
    const original = [...selected.value.permissions].sort();
    const current = [...form.permissions].sort();

    return original.length !== current.length || original.some((name, i) => name !== current[i]);
});

const filteredGroups = computed(() => {
    const needle = filter.value.trim().toLowerCase();
    if (!needle) return props.permissionGroups;

    return Object.fromEntries(
        Object.entries(props.permissionGroups)
            .map(([group, permissions]) => [
                group,
                permissions.filter((permission) => permission.includes(needle)),
            ])
            .filter(([, permissions]) => permissions.length)
    );
});

const allFilteredSelected = computed(() => {
    const filtered = Object.values(filteredGroups.value).flat();
    return filtered.length > 0 && filtered.every((permission) => form.permissions.includes(permission));
});

const resetPermissions = () => {
    form.permissions = [...(selected.value?.permissions ?? [])];
};

const selectAllFiltered = () => {
    const filtered = Object.values(filteredGroups.value).flat();
    const set = new Set(form.permissions);

    if (allFilteredSelected.value) {
        filtered.forEach((permission) => set.delete(permission));
    } else {
        filtered.forEach((permission) => set.add(permission));
    }

    form.permissions = [...set];
};

// Switching roles discards the previous role's unsaved edits.
watch(selected, (role) => {
    if (!role) return;
    form.permissions = [...(role.permissions ?? [])];
});

const submit = () => {
    if (!selected.value) return;

    form.patch(route('hive.roles.update', { role: selected.value.id }));
};
</script>

<template>
  <HiveLayout title="Settings" description="Institution-wide configuration">
    <div class="space-y-6">
      <!-- Group tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex flex-wrap gap-2">
          <button
            v-for="group in groupMeta"
            :key="group.key"
            type="button"
            @click="activeGroup = group.key"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
            :class="tabClass(group.key)"
          >
            {{ group.label }}
            <span class="ml-1 text-xs text-gray-400">({{ group.count }})</span>
          </button>
        </nav>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div
          v-for="group in groupMeta"
          v-show="activeGroup === group.key"
          :key="group.key"
          class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700"
        >
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ group.label }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ group.description }}</p>
          </div>

          <div class="p-6 space-y-5">
            <div v-for="setting in settingsByGroup[group.key]" :key="setting.key">
              <label
                v-if="setting.type !== 'boolean'"
                :for="setting.key"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200"
              >
                {{ setting.label }}
                <span class="ml-1 text-xs font-mono text-gray-400">{{ setting.key }}</span>
              </label>
              <p v-if="setting.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ setting.description }}
              </p>

              <!-- Boolean -->
              <div v-if="setting.type === 'boolean'" class="mt-2 flex items-center gap-3">
                <input
                  :id="setting.key"
                  v-model="form.settings[setting.key]"
                  type="checkbox"
                  class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600"
                />
                <label :for="setting.key" class="text-sm text-gray-700 dark:text-gray-200">
                  {{ setting.label }}
                </label>
              </div>

              <!-- Number -->
              <input
                v-else-if="setting.type === 'integer' || setting.type === 'decimal'"
                :id="setting.key"
                v-model.number="form.settings[setting.key]"
                type="number"
                :step="setting.type === 'decimal' ? '0.01' : '1'"
                class="mt-2 block w-full max-w-sm rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />

              <!-- Select -->
              <select
                v-else-if="setting.key === 'notifications.digest_frequency'"
                :id="setting.key"
                v-model="form.settings[setting.key]"
                class="mt-2 block w-full max-w-sm rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="never">Never</option>
              </select>

              <!-- Text -->
              <input
                v-else
                :id="setting.key"
                v-model="form.settings[setting.key]"
                type="text"
                class="mt-2 block w-full max-w-sm rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
            </div>
          </div>
        </div>

        <EmptyState
          v-if="groupMeta.length === 0"
          type="document"
          title="No settings registered"
          description="Run the SettingSeeder to register the settings this installation can configure."
        />

        <div
          v-else
          class="flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3"
        >
          <p v-if="isDirty" class="text-sm text-amber-700 dark:text-amber-400 mr-auto">
            You have unsaved changes.
          </p>
          <button
            type="button"
            @click="reset"
            :disabled="!isDirty"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"
          >
            Discard
          </button>
          <button
            type="submit"
            :disabled="!isDirty || form.processing"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50"
          >
            <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Save changes
          </button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    settings: {
        type: Array,
        default: () => [],
    },
    groups: {
        type: Array,
        default: () => [],
    },
});

const GROUP_META = {
    institution: { label: 'Institution', description: 'Identity and contact details used on generated documents.' },
    academic: { label: 'Academic', description: 'Defaults applied to programmes, modules and student records.' },
    finance: { label: 'Finance', description: 'Billing, payroll and currency defaults.' },
    hr: { label: 'Human Resources', description: 'Staff leave, probation and appointment defaults.' },
    notifications: { label: 'Notifications', description: 'How and when the system notifies people.' },
};

const activeGroup = ref(props.groups[0] ?? null);

const settingsByGroup = computed(() =>
    props.settings.reduce((acc, setting) => {
        (acc[setting.group] ||= []).push(setting);
        return acc;
    }, {})
);

const groupMeta = computed(() =>
    props.groups
        .filter((group) => settingsByGroup.value[group]?.length)
        .map((group) => ({
            key: group,
            ...(GROUP_META[group] ?? { label: group, description: '' }),
            count: settingsByGroup.value[group].length,
        }))
);

const ACTIVE_TAB = 'border-amber-600 text-amber-700 dark:text-amber-400';
const INACTIVE_TAB = 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';

const tabClass = (key) => (activeGroup.value === key ? ACTIVE_TAB : INACTIVE_TAB);

// Inertia sends booleans through as real booleans, but a checkbox bound to a
// numeric-looking value needs a real boolean to behave.
const initialValues = () =>
    props.settings.reduce((acc, setting) => {
        acc[setting.key] = setting.value;
        return acc;
    }, {});

const form = useForm({ settings: initialValues() });

const isDirty = computed(() =>
    props.settings.some((setting) => form.settings[setting.key] !== setting.value)
);

const reset = () => {
    form.settings = initialValues();
};

watch(
    () => props.settings,
    () => {
        form.settings = initialValues();
    }
);

const submit = () => {
    form.patch(route('hive.settings.update'));
};
</script>

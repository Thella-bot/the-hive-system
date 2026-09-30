<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ArrowDownTrayIcon, QrCodeIcon, FunnelIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  records: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  methods: { type: Array, default: () => ['qr', 'manual'] },
  canViewAll: { type: Boolean, default: false },
  canScan: { type: Boolean, default: false },
});

const search = ref(props.filters.search ?? '');
const method = ref(props.filters.method ?? '');
const eventId = ref(props.filters.event_id ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const showFilters = ref(Boolean(method.value || eventId.value || dateFrom.value || dateTo.value));

const activeFilterCount = computed(
  () => [method.value, eventId.value, dateFrom.value, dateTo.value].filter(Boolean).length
);

const hasFilters = computed(() => Boolean(search.value) || activeFilterCount.value > 0);

const currentFilters = () => ({
  ...(search.value ? { search: search.value } : {}),
  ...(method.value ? { method: method.value } : {}),
  ...(eventId.value ? { event_id: eventId.value } : {}),
  ...(dateFrom.value ? { date_from: dateFrom.value } : {}),
  ...(dateTo.value ? { date_to: dateTo.value } : {}),
});

const applyFilters = () =>
  router.get(route('hive.attendance.index'), currentFilters(), {
    preserveState: true,
    replace: true,
  });

// Dropdowns and dates apply straight away; SearchInput debounces its own typing.
watch([method, eventId, dateFrom, dateTo], applyFilters);

const clearFilters = () => {
  search.value = '';
  method.value = '';
  eventId.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  applyFilters();
};

const exportHref = computed(() => {
  const params = new URLSearchParams(currentFilters()).toString();
  const base = route('hive.attendance.export');
  return params ? `${base}?${params}` : base;
});

const rows = computed(() => props.records.data ?? []);

const METHOD_LABELS = {
  qr: 'QR scan',
  manual: 'Manual',
};

const methodLabel = (value) => METHOD_LABELS[value] ?? value ?? '—';

const formatDateTime = (value) =>
  value
    ? new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    : '—';
</script>

<template>
  <HiveLayout
    title="Attendance"
    :description="canViewAll
      ? 'Every check-in across the institute'
      : 'Your own check-in history'"
  >
    <template #header-actions>
      <a
        :href="exportHref"
        class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors"
        title="Download the attendance register as CSV, using the filters currently applied"
      >
        <ArrowDownTrayIcon class="w-4 h-4" />
        Export CSV
      </a>
      <Link
        v-if="canScan"
        :href="route('hive.attendance.scan')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
      >
        <QrCodeIcon class="w-4 h-4" />
        Scan
      </Link>
    </template>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-4">
      <div class="flex flex-col md:flex-row gap-3 md:items-center">
        <div class="flex-1">
          <SearchInput
            v-model="search"
            placeholder="Search by name, email or student number..."
            @search="applyFilters"
          />
        </div>
        <button
          type="button"
          @click="showFilters = !showFilters"
          class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          :aria-expanded="showFilters"
        >
          <FunnelIcon class="w-4 h-4" />
          Filters
          <span
            v-if="activeFilterCount > 0"
            class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200"
          >
            {{ activeFilterCount }}
          </span>
        </button>
      </div>

      <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
        <div>
          <label for="attendance-method" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Method</label>
          <select
            id="attendance-method"
            v-model="method"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none dark:bg-gray-700 dark:text-white"
          >
            <option value="">All methods</option>
            <option v-for="value in methods" :key="value" :value="value">{{ methodLabel(value) }}</option>
          </select>
        </div>
        <div>
          <label for="attendance-event" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Event ID</label>
          <input
            id="attendance-event"
            v-model="eventId"
            type="number"
            min="1"
            placeholder="e.g. 42"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div>
          <label for="attendance-from" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">From</label>
          <input
            id="attendance-from"
            v-model="dateFrom"
            type="date"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div>
          <label for="attendance-to" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">To</label>
          <input
            id="attendance-to"
            v-model="dateTo"
            type="date"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div v-if="hasFilters" class="sm:col-span-2 lg:col-span-4">
          <button
            type="button"
            @click="clearFilters"
            class="inline-flex items-center gap-1 text-sm text-amber-600 hover:text-amber-700"
          >
            <XMarkIcon class="w-4 h-4" />
            Clear all filters
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Student
            </th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden sm:table-cell">
              Event
            </th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Method
            </th>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Checked in
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="rows.length === 0">
            <td colspan="4" class="px-6 py-12 text-center">
              <EmptyState
                type="calendar"
                :title="hasFilters ? 'No check-ins match these filters' : 'No check-ins yet'"
                :description="hasFilters
                  ? 'Try widening the date range or clearing the filters.'
                  : 'Check-ins appear here as soon as they are recorded.'"
              />
              <p v-if="hasFilters" class="text-sm text-gray-400 dark:text-gray-500 -mt-6">
                <button type="button" @click="clearFilters" class="text-amber-600 hover:text-amber-700">Clear the filters</button>
                to see everything
              </p>
            </td>
          </tr>
          <tr
            v-for="record in rows"
            :key="record.id"
            class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
          >
            <td class="px-6 py-4">
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ record.user?.name ?? '—' }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                <span v-if="record.user?.student_number" class="font-mono">{{ record.user.student_number }}</span>
                <span v-else>{{ record.user?.email ?? '—' }}</span>
              </p>
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 hidden sm:table-cell">
              {{ record.event?.title ?? (record.event_type ? record.event_type.replace(/_/g, ' ') : '—') }}
            </td>
            <td class="px-6 py-4">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="record.method === 'qr'
                  ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
                  : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
              >
                {{ methodLabel(record.method) }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ formatDateTime(record.checked_in_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <p class="text-xs text-gray-500 dark:text-gray-400">
        Exporting downloads the {{ records.total ?? 0 }} check-in{{ (records.total ?? 0) === 1 ? '' : 's' }} matching these filters.
      </p>
      <Pagination v-if="rows.length > 0" :links="records.links" :meta="records" />
    </div>
  </HiveLayout>
</template>

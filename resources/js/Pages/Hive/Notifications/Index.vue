<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const props = defineProps({
    notifications: Object,
    unreadCount: { type: Number, default: 0 },
    filters: {
        type: Object,
        default: () => ({ type: null, status: 'all' }),
    },
    availableTypes: {
        type: Array,
        default: () => [],
    },
});

const selected = ref([]);

const rows = computed(() => props.notifications?.data ?? []);

const allSelected = computed(() =>
    rows.value.length > 0 && rows.value.every((n) => selected.value.includes(n.id))
);

const someSelected = computed(() => selected.value.length > 0 && !allSelected.value);

const statusCounts = computed(() => {
    const page = props.notifications?.meta ?? {};
    return {
        total: page.total ?? rows.value.length,
        from: page.from ?? 1,
        to: page.to ?? rows.value.length,
    };
});

const toggleAll = () => {
    selected.value = allSelected.value ? [] : rows.value.map((n) => n.id);
};

const isSelected = (id) => selected.value.includes(id);

const toggleOne = (id) => {
    selected.value = isSelected(id)
        ? selected.value.filter((value) => value !== id)
        : [...selected.value, id];
};

const markRead = (id) => {
    router.post(route('hive.notifications.read', { notification: id }), {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post(route('hive.notifications.readAll'), {}, { preserveScroll: true });
};

const markSelectedRead = () => {
    router.post(
        route('hive.notifications.markSelectedRead'),
        { ids: selected.value },
        { preserveScroll: true, onSuccess: () => { selected.value = []; } }
    );
};

const deleteSelected = () => {
    router.delete(route('hive.notifications.destroySelected'), {
        ids: selected.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { selected.value = []; },
    });
};

const applyFilters = (next) => {
    router.get(route('hive.notifications.index'), {
        type: next.type ?? undefined,
        status: next.status ?? 'all',
    }, { preserveScroll: true, replace: true });
};

const setStatus = (status) => applyFilters({ ...props.filters, status });
const setType = (type) => applyFilters({ ...props.filters, type: type === props.filters.type ? null : type });

const clearFilters = () => applyFilters({ type: null, status: 'all' });

const hasFilters = computed(() => props.filters.type || props.filters.status !== 'all');

const formatDate = (date) => {
    if (!date) return '';
    return dayjs(date).fromNow();
};

const notificationIcon = (type) => {
    if (type?.includes('Application')) return '📋';
    if (type?.includes('Leave')) return '🏖️';
    if (type?.includes('Submission')) return '📤';
    if (type?.includes('Announcement')) return '📢';
    if (type?.includes('User')) return '👤';
    return '🔔';
};

const STATUS_OPTIONS = [
    { value: 'all', label: 'All' },
    { value: 'unread', label: 'Unread' },
    { value: 'read', label: 'Read' },
];

const CHIP_ACTIVE = 'bg-amber-600 text-white';
const CHIP_IDLE = 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600';

const statusClass = (value) => (value === props.filters.status ? CHIP_ACTIVE : CHIP_IDLE);
const typeClass = (value) => (value === props.filters.type ? CHIP_ACTIVE : CHIP_IDLE);

// A page change must not carry stale selections into the new result set.
watch(() => props.notifications?.data, () => {
    selected.value = [];
});
</script>

<template>
    <HiveLayout title="Notifications" :description="`${unreadCount} unread`">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Notifications</h1>
                    <p v-if="unreadCount > 0" class="text-sm text-amber-600 dark:text-amber-400 mt-0.5">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="text-sm text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium px-3 py-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                    Mark all as read
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <button
                    v-for="option in STATUS_OPTIONS"
                    :key="option.value"
                    type="button"
                    @click="setStatus(option.value)"
                    class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                    :class="statusClass(option.value)"
                >
                    {{ option.label }}
                </button>

                <span v-if="availableTypes.length" class="mx-1 h-4 w-px bg-gray-200 dark:bg-gray-700" />

                <button
                    v-for="option in availableTypes"
                    :key="option.value"
                    type="button"
                    @click="setType(option.value)"
                    class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                    :class="typeClass(option.value)"
                >
                    {{ option.label }}
                </button>

                <button
                    v-if="hasFilters"
                    type="button"
                    @click="clearFilters"
                    class="text-xs text-gray-500 hover:text-gray-700 underline dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Clear
                </button>

                <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                    Showing {{ statusCounts.from }}–{{ statusCounts.to }} of {{ statusCounts.total }}
                </span>
            </div>

            <!-- Bulk action bar -->
            <div
                v-if="selected.length > 0"
                class="flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 mb-3 dark:border-amber-700 dark:bg-amber-900/20"
            >
                <span class="text-sm font-medium text-amber-800 dark:text-amber-300">
                    {{ selected.length }} selected
                </span>
                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        @click="markSelectedRead"
                        class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700"
                    >
                        Mark read
                    </button>
                    <button
                        type="button"
                        @click="deleteSelected"
                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20"
                    >
                        Delete
                    </button>
                    <button
                        type="button"
                        @click="selected = []"
                        class="px-2 py-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Notifications list -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <EmptyState
                    v-if="!rows.length"
                    type="notification"
                    :title="hasFilters ? 'No notifications match these filters' : 'No notifications'"
                    :description="hasFilters
                        ? 'Try a different status or type.'
                        : \"You're all caught up.\"
                />

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                    <!-- Select-all header row -->
                    <div class="flex items-center gap-3 px-6 py-2.5 bg-gray-50 dark:bg-gray-700/50">
                        <input
                            type="checkbox"
                            :checked="allSelected"
                            :indeterminate.prop="someSelected"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600"
                            aria-label="Select all notifications"
                        />
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ allSelected ? 'Deselect all' : 'Select all on this page' }}
                        </span>
                    </div>

                    <div
                        v-for="notification in rows"
                        :key="notification.id"
                        class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        :class="{ 'bg-amber-50/50 dark:bg-amber-900/20': !notification.read_at }">

                        <input
                            type="checkbox"
                            :checked="isSelected(notification.id)"
                            @change="toggleOne(notification.id)"
                            class="mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600"
                            :aria-label="`Select notification ${notification.id}`"
                        />

                        <!-- Icon -->
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-full flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
                            {{ notificationIcon(notification.type) }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-snug">
                                    {{ notification.data?.message || notification.data?.title || 'Notification' }}
                                </p>
                                <span v-if="!notification.read_at" class="w-2 h-2 bg-amber-500 rounded-full flex-shrink-0 mt-1.5"></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                        </div>

                        <!-- Action -->
                        <button
                            v-if="!notification.read_at"
                            @click="markRead(notification.id)"
                            class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium whitespace-nowrap hover:bg-amber-50 dark:hover:bg-amber-900/30 px-2 py-1 rounded transition-colors">
                            Mark read
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="rows.length > 0"
                :links="notifications.links"
                :meta="notifications.meta"
                class="mt-4" />
        </div>
    </HiveLayout>
</template>

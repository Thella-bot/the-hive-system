<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
      :aria-expanded="open"
      aria-haspopup="true"
      :aria-label="`Notifications${unreadCount ? `, ${unreadCount} unread` : ''}`"
      title="Notifications"
      @click="toggle"
    >
      <BellIcon class="h-5 w-5" />
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="open"
      class="absolute right-0 z-50 mt-2 w-96 origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
        <p class="text-sm font-semibold text-gray-800 dark:text-white">
          Notifications
          <span v-if="unreadCount" class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">
            {{ unreadCount }} unread
          </span>
        </p>
        <button
          type="button"
          class="text-xs font-medium text-amber-600 hover:text-amber-700 disabled:opacity-50 dark:text-amber-400 dark:hover:text-amber-300"
          :disabled="unreadCount === 0"
          @click="markAllRead"
        >
          Mark all read
        </button>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div v-if="loading" class="px-4 py-8 text-center">
          <svg class="mx-auto h-5 w-5 animate-spin text-amber-500" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
        </div>

        <p v-else-if="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          You are all caught up.
        </p>

        <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
          <li v-for="item in notifications" :key="item.id">
            <component
              :is="item.url ? 'a' : 'div'"
              :href="item.url || undefined"
              class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700"
              :class="item.read_at ? '' : 'bg-amber-50/60 dark:bg-amber-900/20'"
              @click="handleItemClick(item, $event)"
            >
              <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs dark:bg-amber-900/50">
                  {{ initial(item.message) }}
                </span>
                <div class="min-w-0 flex-1">
                  <p class="text-sm text-gray-800 dark:text-gray-100">{{ item.message }}</p>
                  <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {{ item.type }} · {{ relative(item.created_at) }}
                  </p>
                </div>
                <span v-if="!item.read_at" class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-amber-500" />
              </div>
            </component>
          </li>
        </ul>
      </div>

      <div class="border-t border-gray-100 px-4 py-2 dark:border-gray-700">
        <Link
          :href="route('hive.notifications.index')"
          class="block text-center text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
          @click="open = false"
        >
          View all notifications
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { BellIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
  // Seconds between background refreshes of the badge count.
  pollInterval: {
    type: Number,
    default: 120,
  },
});

const page = usePage();

const root = ref(null);
const open = ref(false);
const loading = ref(false);
const notifications = ref([]);
const unreadCount = ref(page.props.unreadNotificationsCount ?? 0);

let pollTimer = null;

const initial = (message) => (message ? String(message).charAt(0).toUpperCase() : '•');
const relative = (date) => (date ? dayjs(date).fromNow() : '');

const fetchPreview = async () => {
  loading.value = true;
  try {
    const response = await fetch(route('hive.notifications.preview'), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) return;

    const payload = await response.json();
    notifications.value = payload.data ?? [];
    unreadCount.value = payload.unread_count ?? 0;
  } catch {
    // A failed preview should never break the header; the badge keeps its
    // server-rendered value and the full page still works.
  } finally {
    loading.value = false;
  }
};

const toggle = () => {
  open.value = !open.value;
  if (open.value) fetchPreview();
};

const markAllRead = () => {
  router.post(route('hive.notifications.readAll'), {}, { preserveScroll: true });
  notifications.value = notifications.value.map((item) => ({ ...item, read_at: item.read_at ?? new Date().toISOString() }));
  unreadCount.value = 0;
};

const handleItemClick = (item, event) => {
  if (!item.read_at) {
    router.post(route('hive.notifications.read', { notification: item.id }), {}, { preserveScroll: true });
    item.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  }

  if (!item.url) {
    event.preventDefault();
  }

  open.value = false;
};

const onDocumentClick = (event) => {
  if (!open.value) return;
  if (root.value && !root.value.contains(event.target)) {
    open.value = false;
  }
};

const onEscape = (event) => {
  if (event.key === 'Escape') open.value = false;
};

onMounted(() => {
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('keydown', onEscape);

  if (props.pollInterval > 0) {
    pollTimer = window.setInterval(() => {
      if (!open.value) fetchPreview();
    }, props.pollInterval * 1000);
  }
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('keydown', onEscape);
  if (pollTimer) window.clearInterval(pollTimer);
});

// A page visit carries a fresh server-rendered count.
watch(
  () => page.props.unreadNotificationsCount,
  (value) => {
    unreadCount.value = value ?? 0;
  }
);
</script>

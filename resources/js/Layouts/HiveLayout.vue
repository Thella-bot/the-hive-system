<template>
  <div class="flex min-h-screen bg-hbci-cream dark:bg-gray-900">
    <!-- Emergency Broadcast Modal -->
    <Transition name="fade">
      <div v-if="emergencyAlert" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90">
        <div class="mx-4 w-full max-w-lg rounded-2xl border-2 border-red-500 bg-white p-8 shadow-2xl">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-5xl">🚨</span>
            <div>
              <h2 class="text-2xl font-bold text-red-600 uppercase tracking-wide">Emergency Alert</h2>
              <p class="text-sm text-gray-500">{{ formatDateTime(emergencyAlert.created_at) }}</p>
            </div>
          </div>
          <h3 class="mb-3 text-xl font-bold text-gray-900">{{ emergencyAlert.title }}</h3>
          <p class="mb-6 whitespace-pre-line text-gray-700">{{ emergencyAlert.body }}</p>
          <button @click="emergencyAlert = null"
            class="w-full rounded-lg bg-red-600 px-4 py-3 font-semibold text-white hover:bg-red-700 transition">
            I Have Read This — Dismiss
          </button>
        </div>
      </div>
    </Transition>

    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-gradient-to-b from-hbci-warm-800 to-hbci-gray text-white transition-transform duration-200 lg:static lg:w-64 lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="px-6 pb-4 pt-6">
        <div class="flex items-start justify-between gap-3">
          <Link :href="route('hive.dashboard')" class="flex flex-1 flex-col items-center gap-3" @click="sidebarOpen = false">
            <img src="/images/hbci-logo-no-text.png" alt="The Hive" class="h-20 w-auto" />
            <div class="text-center">
              <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-amber-400">Honey Bee Culinary Institute</div>
              <div class="mt-0.5 text-[11px] font-semibold tracking-wide text-gray-300">The Hive</div>
            </div>
          </Link>
          <button
            type="button"
            class="rounded-md p-1 text-gray-400 hover:bg-gray-800 hover:text-white lg:hidden"
            aria-label="Close navigation"
            @click="sidebarOpen = false"
          >
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div class="mx-6 border-t border-gray-700" />

      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <template v-for="item in singleItems" :key="item.name">
          <NavItem :href="item.href" :active="item.isActive ? item.isActive() : item.active" @click="sidebarOpen = false">
            <template #icon>
              <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
            </template>
            {{ item.name }}
          </NavItem>
        </template>

        <template v-for="category in categories" :key="category.name">
          <div>
            <button
              type="button"
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
              @click="toggleCategory(category.name)"
            >
              <span class="flex items-center gap-2">
                <component :is="category.icon" v-if="category.icon" class="h-4 w-4" />
                {{ category.name }}
              </span>
              <ChevronDownIcon
                class="h-4 w-4 transform transition-transform duration-200"
                :class="{ 'rotate-180': expandedCategories.includes(category.name) }"
              />
            </button>

            <div v-if="expandedCategories.includes(category.name)" class="mt-1 space-y-1">
              <NavItem
                v-for="child in category.children"
                :key="child.name"
                :href="child.href"
                :target="child.target"
                :active="child.isActive ? child.isActive() : child.active"
                class="pl-9"
                @click="sidebarOpen = false"
              >
                {{ child.name }}
              </NavItem>
            </div>
          </div>
        </template>
      </nav>

      <div class="border-t border-gray-700">
        <div v-if="currentUser" class="p-4">
          <div class="flex items-center gap-3">
            <img
              :src="currentUser.profile_photo_url"
              :alt="currentUser.name"
              class="h-8 w-8 flex-shrink-0 rounded-full object-cover"
            />
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium">{{ currentUser.name }}</p>
              <p class="truncate text-xs capitalize text-gray-400">{{ displayRole }}</p>
            </div>
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="rounded-md p-1 text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
              title="Logout"
            >
              <ArrowRightOnRectangleIcon class="h-5 w-5" />
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
      <header class="flex-shrink-0 border-b border-gray-100 bg-white px-4 py-4 sm:px-6 lg:px-8 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex min-w-0 items-center gap-4">
            <button
              type="button"
              class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-50 lg:hidden"
              aria-label="Open navigation"
              @click="sidebarOpen = true"
            >
              <Bars3Icon class="h-5 w-5" />
            </button>
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
              <p v-if="description" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form class="relative w-full sm:w-80" @submit.prevent="submitSearch">
              <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <input
                v-model="searchQuery"
                type="search"
                autocomplete="off"
                class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                placeholder="Search The Hive"
              />
            </form>

            <div class="flex items-center justify-end gap-2">
              <ThemeToggle />
              <Link
                :href="route('hive.notifications.index')"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                title="Notifications"
              >
                <BellIcon class="h-5 w-5" />
                <span
                  v-if="$page.props.unreadNotificationsCount > 0"
                  class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900"
                >
                  {{ $page.props.unreadNotificationsCount > 9 ? '9+' : $page.props.unreadNotificationsCount }}
                </span>
              </Link>
              <Link
                :href="route('hive.profile.edit')"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                title="Profile"
              >
                <UserCircleIcon class="h-5 w-5" />
              </Link>
              <slot name="header-actions" />
            </div>
          </div>
        </div>
      </header>

      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-4 pt-4 sm:px-6 lg:px-8">
        <div
          v-if="$page.props.flash?.success"
          class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-200"
        >
          <CheckCircleIcon class="h-5 w-5 flex-shrink-0 text-green-500" />
          {{ $page.props.flash.success }}
        </div>
        <div
          v-if="$page.props.flash?.error"
          class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
          {{ $page.props.flash.error }}
        </div>
      </div>

      <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8 bg-hbci-cream dark:bg-gray-900">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import NavItem from '@/Components/NavItem.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { useNavigation } from '@/composables/useNavigation';
import { useUser } from '@/composables/useUser';
import {
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  BellIcon,
  CheckCircleIcon,
  ChevronDownIcon,
  ExclamationCircleIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  MegaphoneIcon,
  UserCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
});

const page = usePage();
const searchQuery = ref('');
const emergencyAlert = ref(null);

const { currentUser, displayRole } = useUser();

const {
  sidebarOpen,
  expandedCategories,
  toggleCategory,
  navigation,
  singleItems,
  categories,
  autoExpandActiveCategory,
} = useNavigation();

const formatDateTime = (iso) => {
  return new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};

onMounted(() => {
  autoExpandActiveCategory();

  if (window.Echo) {
    window.Echo.private('emergency')
      .listen('EmergencyAlert', (e) => {
        emergencyAlert.value = e;
      });
  }
});

const submitSearch = () => {
  const query = searchQuery.value.trim();
  if (!query) return;
  router.get(route('hive.search'), { query });
};
</script>
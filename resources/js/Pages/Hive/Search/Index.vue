<template>
  <HiveLayout title="Search" :description="query ? `${total} result${total === 1 ? '' : 's'} for '${query}'` : 'Search across The Hive'">
    <form @submit.prevent="submit" class="mb-6 flex max-w-3xl gap-3">
      <div class="relative flex-1">
        <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
        <input
          v-model="form.query"
          type="search"
          name="query"
          autocomplete="off"
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 pl-10 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:ring-amber-500"
          placeholder="Search people, modules, documents, announcements..."
        />
      </div>
      <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700"
      >
        <MagnifyingGlassIcon class="h-4 w-4" />
        Search
      </button>
    </form>

    <div v-if="!query" class="rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-10 text-center">
      <MagnifyingGlassIcon class="mx-auto h-10 w-10 text-amber-500" />
      <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">Find anything in the institute</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use a student number, module code, document title, event name, or assessment keyword.</p>
    </div>

    <div v-else-if="total === 0" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
      <DocumentMagnifyingGlassIcon class="mx-auto h-10 w-10 text-gray-400" />
      <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">No results found</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try a shorter phrase or a module code.</p>
    </div>

    <div v-else class="space-y-5">
      <!-- Type filters + keyboard hint -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="activeTypes = []"
          class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
          :class="activeTypes.length === 0
            ? 'bg-amber-600 text-white'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
        >
          All ({{ total }})
        </button>
        <button
          v-for="section in sections"
          :key="section.key"
          type="button"
          @click="toggleType(section.key)"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition-colors"
          :class="activeTypes.includes(section.key)
            ? 'bg-amber-600 text-white'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
        >
          <component :is="section.icon" class="h-3.5 w-3.5" />
          {{ section.label }} ({{ section.items.length }})
        </button>

        <p class="ml-auto hidden items-center gap-3 text-xs text-gray-400 dark:text-gray-500 sm:flex">
          <span><kbd class="rounded border border-gray-300 dark:border-gray-600 px-1">↑</kbd> <kbd class="rounded border border-gray-300 dark:border-gray-600 px-1">↓</kbd> navigate</span>
          <span><kbd class="rounded border border-gray-300 dark:border-gray-600 px-1">Enter</kbd> open</span>
        </p>
      </div>

      <div v-if="filteredSections.length === 0" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
        <DocumentMagnifyingGlassIcon class="mx-auto h-10 w-10 text-gray-400" />
        <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">No results in this category</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try selecting "All" to see every match.</p>
      </div>

      <div v-else class="grid gap-5 lg:grid-cols-2">
        <section
          v-for="section in filteredSections"
          :key="section.key"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm"
        >
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-5 py-4">
            <div class="flex items-center gap-2">
              <component :is="section.icon" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
              <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ section.label }}</h2>
            </div>
            <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-400">{{ section.items.length }}</span>
          </div>

          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <Link
              v-for="item in section.items"
              :key="`${section.key}-${item.id}`"
              :href="item.url"
              :ref="(el) => registerResult(section.key, item.id, el)"
              class="block px-5 py-4 hover:bg-amber-50 dark:hover:bg-amber-900/20"
              :class="resultClass(section.key, item.id)"
            >
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ item.title }}</p>
              <p v-if="item.meta" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ item.meta }}</p>
            </Link>
          </div>
        </section>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import {
  AcademicCapIcon,
  BellAlertIcon,
  CalendarDaysIcon,
  ClipboardDocumentListIcon,
  DocumentMagnifyingGlassIcon,
  FolderIcon,
  MagnifyingGlassIcon,
  UsersIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  query: {
    type: String,
    default: '',
  },
  results: {
    type: Object,
    default: () => ({}),
  },
  total: {
    type: Number,
    default: 0,
  },
});

const form = reactive({
  query: props.query,
});

const sections = computed(() => [
  { key: 'people', label: 'People', icon: UsersIcon, items: props.results.people || [] },
  { key: 'modules', label: 'Modules', icon: AcademicCapIcon, items: props.results.modules || [] },
  { key: 'documents', label: 'Documents', icon: FolderIcon, items: props.results.documents || [] },
  { key: 'assessments', label: 'Assessments', icon: ClipboardDocumentListIcon, items: props.results.assessments || [] },
  { key: 'announcements', label: 'Announcements', icon: BellAlertIcon, items: props.results.announcements || [] },
  { key: 'events', label: 'Events', icon: CalendarDaysIcon, items: props.results.events || [] },
]);

// Empty means "all types", which is the default view.
const activeTypes = ref([]);

const filteredSections = computed(() => {
  if (activeTypes.value.length === 0) return sections.value.filter((s) => s.items.length > 0);
  return sections.value.filter((s) => activeTypes.value.includes(s.key) && s.items.length > 0);
});

const visibleSections = computed(() => sections.value.filter((section) => section.items.length > 0));

const toggleType = (key) => {
  activeTypes.value = activeTypes.value.includes(key)
    ? activeTypes.value.filter((k) => k !== key)
    : [...activeTypes.value, key];
  activeIndex.value = 0;
};

// --- Keyboard navigation ---

const resultElements = new Map();
const activeIndex = ref(0);

const registerResult = (sectionKey, itemId, el) => {
  const key = `${sectionKey}-${itemId}`;
  // Inertia's <Link> is a component, so the ref is a component instance.
  // Fall back to its root element for scrollIntoView.
  const element = el?.$el ?? el;

  if (element?.scrollIntoView) {
    resultElements.set(key, element);
  } else {
    resultElements.delete(key);
  }
};

/** Flat list of every rendered result, in visual order. */
const flatResults = computed(() =>
  filteredSections.value.flatMap((section) =>
    section.items.map((item) => ({ key: `${section.key}-${item.id}`, url: item.url }))
  )
);

const isActive = (sectionKey, itemId) => {
  const entry = flatResults.value[activeIndex.value];
  return entry?.key === `${sectionKey}-${itemId}`;
};

const ACTIVE_RESULT_CLASS = 'bg-amber-50 ring-1 ring-inset ring-amber-400 dark:bg-amber-900/20 dark:ring-amber-500';

const resultClass = (sectionKey, itemId) => (isActive(sectionKey, itemId) ? ACTIVE_RESULT_CLASS : '');

const scrollActiveIntoView = () => {
  const entry = flatResults.value[activeIndex.value];
  if (!entry) return;
  resultElements.get(entry.key)?.scrollIntoView({ block: 'nearest' });
};

const onKeydown = (event) => {
  if (!flatResults.value.length) return;

  const tag = document.activeElement?.tagName?.toLowerCase();
  const typing = ['input', 'textarea', 'select'].includes(tag) || document.activeElement?.isContentEditable;
  if (typing) return;

  if (event.key === 'ArrowDown') {
    event.preventDefault();
    activeIndex.value = (activeIndex.value + 1) % flatResults.value.length;
    scrollActiveIntoView();
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    activeIndex.value = (activeIndex.value - 1 + flatResults.value.length) % flatResults.value.length;
    scrollActiveIntoView();
  } else if (event.key === 'Enter') {
    event.preventDefault();
    const entry = flatResults.value[activeIndex.value];
    if (entry) router.visit(entry.url);
  }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown);
  resultElements.clear();
});

watch(filteredSections, () => {
  if (activeIndex.value >= flatResults.value.length) {
    activeIndex.value = 0;
  }
});

const submit = () => {
  router.get(route('hive.search'), { query: form.query }, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

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
          class="w-full rounded-lg border-gray-300 pl-10 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
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

    <div v-if="!query" class="rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
      <MagnifyingGlassIcon class="mx-auto h-10 w-10 text-amber-500" />
      <h2 class="mt-3 text-lg font-semibold text-gray-900">Find anything in the institute</h2>
      <p class="mt-1 text-sm text-gray-500">Use a student number, module code, document title, event name, or assessment keyword.</p>
    </div>

    <div v-else-if="total === 0" class="rounded-lg border border-gray-200 bg-white p-10 text-center">
      <DocumentMagnifyingGlassIcon class="mx-auto h-10 w-10 text-gray-400" />
      <h2 class="mt-3 text-lg font-semibold text-gray-900">No results found</h2>
      <p class="mt-1 text-sm text-gray-500">Try a shorter phrase or a module code.</p>
    </div>

    <div v-else class="grid gap-5 lg:grid-cols-2">
      <section
        v-for="section in visibleSections"
        :key="section.key"
        class="rounded-lg border border-gray-200 bg-white shadow-sm"
      >
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <component :is="section.icon" class="h-5 w-5 text-amber-600" />
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ section.label }}</h2>
          </div>
          <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ section.items.length }}</span>
        </div>

        <div class="divide-y divide-gray-100">
          <Link
            v-for="item in section.items"
            :key="`${section.key}-${item.id}`"
            :href="item.url"
            class="block px-5 py-4 hover:bg-amber-50"
          >
            <p class="text-sm font-semibold text-gray-900">{{ item.title }}</p>
            <p v-if="item.meta" class="mt-1 text-xs text-gray-500">{{ item.meta }}</p>
          </Link>
        </div>
      </section>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed, reactive } from 'vue';
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

const visibleSections = computed(() => sections.value.filter((section) => section.items.length > 0));

const submit = () => {
  router.get(route('hive.search'), { query: form.query }, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

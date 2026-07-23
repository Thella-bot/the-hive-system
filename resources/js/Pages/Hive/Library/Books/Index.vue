<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
  books: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');

const applyFilters = () => {
  router.get(route('library.books.index'),
    { search: search.value, category_id: props.filters.category_id, available: props.filters.available },
    { preserveState: true, replace: true }
  );
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-LS', { style: 'currency', currency: 'ZAR' }).format(amount);
};
</script>

<template>
  <HiveLayout title="Library Books" description="Browse and manage library books">
    <template #header-actions>
      <Link :href="route('library.books.create')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors">
        <PlusIcon class="w-5 h-5" />
        Add Book
      </Link>
    </template>

    <div class="mb-5 flex flex-wrap gap-3 items-center justify-between">
      <div class="flex gap-3 items-center">
        <div class="max-w-xs">
          <SearchInput v-model="search" @search="applyFilters" placeholder="Search books..." />
        </div>
        <select v-model="filters.category_id" @change="applyFilters"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
          <input type="checkbox" v-model="filters.available" @change="applyFilters" class="rounded border-gray-300" />
          Available only
        </label>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Title</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Author</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">ISBN</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Category</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Copies</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="books.data.length === 0">
            <td colspan="6">
              <EmptyState type="book" title="No books found" />
            </td>
          </tr>
          <tr v-for="book in books.data" :key="book.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ book.call_number || '—' }}</p>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ book.author }}</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span class="text-gray-500 dark:text-gray-400">{{ book.isbn || '—' }}</span>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <span class="text-gray-600 dark:text-gray-400">{{ book.category?.name || '—' }}</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span :class="book.available_copies > 0 ? 'text-green-600' : 'text-red-600'"
                  class="font-medium">{{ book.available_copies }}</span>
                <span class="text-gray-400">/ {{ book.total_copies }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Link :href="route('library.books.show', book.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <EyeIcon class="w-5 h-5" />
                </Link>
                <Link :href="route('library.books.edit', book.id)" class="p-1 text-gray-500 hover:text-amber-600">
                  <PencilSquareIcon class="w-5 h-5" />
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :pagination="books" />
    </div>
  </HiveLayout>
</template>
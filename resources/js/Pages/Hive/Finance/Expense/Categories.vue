<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import {
  ArrowLeftIcon,
  PlusIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  categories: { type: Array, required: true },
  errors: { type: Object, default: () => ({}) },
});

const showCreateModal = ref(false);
const editCategory = ref(null);

const newCategory = ref({
  name: '',
  code: '',
  description: '',
});

const createCategory = () => {
  router.post(route('finance.expenses.categories.store'), newCategory.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      newCategory.value = { name: '', code: '', description: '' };
    }
  });
};

const toggleEdit = (category) => {
  editCategory.value = editCategory.value?.id === category.id ? null : { ...category };
};

const updateCategory = () => {
  router.patch(route('finance.expenses.categories.update', editCategory.value.id), editCategory.value, {
    onSuccess: () => { editCategory.value = null; }
  });
};

const deleteCategory = (category) => {
  if (confirm(`Delete category "${category.name}"?`)) {
    router.delete(route('finance.expenses.categories.destroy', category.id));
  }
};
</script>

<template>
  <HiveLayout title="Expense Categories" description="Manage expense categories">
    <template #header-actions>
      <Link :href="route('finance.expenses.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Expenses
      </Link>
    </template>

    <div class="mb-6 flex justify-end">
      <button
        @click="showCreateModal = true"
        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium"
      >
        <PlusIcon class="w-5 h-5" />
        New Category
      </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Code</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Description</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="categories.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
              No categories found
            </td>
          </tr>
          <tr v-for="category in categories" :key="category.id">
            <template v-if="editCategory?.id === category.id">
              <td colspan="5" class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                <div class="flex gap-3 items-center">
                  <input
                    v-model="editCategory.code"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                    placeholder="Code"
                  />
                  <input
                    v-model="editCategory.name"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                    placeholder="Name"
                  />
                  <label class="flex items-center gap-2">
                    <input v-model="editCategory.is_active" type="checkbox" class="rounded" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                  </label>
                  <button
                    @click="updateCategory"
                    class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm"
                  >
                    Save
                  </button>
                  <button
                    @click="editCategory = null"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 text-sm"
                  >
                    Cancel
                  </button>
                </div>
              </td>
            </template>
            <template v-else>
              <td class="px-6 py-4">
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ category.code }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-gray-900 dark:text-gray-100">{{ category.name }}</span>
              </td>
              <td class="px-6 py-4 hidden md:table-cell">
                <span class="text-gray-500 dark:text-gray-400">{{ category.description || '—' }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="category.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button @click="toggleEdit(category)" class="p-1 text-gray-500 hover:text-amber-600">
                    <PencilSquareIcon class="w-5 h-5" />
                  </button>
                  <button @click="deleteCategory(category)" class="p-1 text-gray-500 hover:text-red-600">
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">New Category</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
            <input
              v-model="newCategory.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Category name"
            />
            <InputError :message="errors.name" class="mt-1" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
            <input
              v-model="newCategory.code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="e.g. SUPP"
            />
            <InputError :message="errors.code" class="mt-1" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea
              v-model="newCategory.description"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Optional description"
            ></textarea>
          </div>
          <div class="flex gap-3">
            <button
              @click="createCategory"
              class="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium"
            >
              Create
            </button>
            <button
              @click="showCreateModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>
<script setup>
import { Link, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ArrowLeftIcon, PencilSquareIcon, TrashIcon, ClockIcon, BookmarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  book: { type: Object, required: true },
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-ZA');
};

const deleteBook = () => {
  if (confirm('Are you sure you want to delete this book?')) {
    router.delete(route('library.books.destroy', props.book.id));
  }
};
</script>

<template>
  <HiveLayout :title="book.title" :description="'by ' + book.author">
    <template #header-actions>
      <div class="flex gap-2">
        <Link :href="route('library.books.edit', book.id)"
          class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
          <PencilSquareIcon class="w-4 h-4" />
          Edit
        </Link>
        <button @click="deleteBook"
          class="inline-flex items-center gap-2 px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
          <TrashIcon class="w-4 h-4" />
          Delete
        </button>
      </div>
    </template>

    <div class="mb-6">
      <Link :href="route('library.books.index')"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        <ArrowLeftIcon class="w-4 h-4" />
        Back to Books
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Book Info -->
      <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-start gap-6">
            <div v-if="book.cover_image" class="w-32 h-48 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
              <img :src="book.cover_image" :alt="book.title" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-32 h-48 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              <span class="text-4xl text-gray-400">📚</span>
            </div>
            <div class="flex-1">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ book.title }}</h1>
              <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">by {{ book.author }}</p>

              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">ISBN:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.isbn || '—' }}</p>
                </div>
                <div>
                  <span class="text-gray-500">Publisher:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.publisher || '—' }}</p>
                </div>
                <div>
                  <span class="text-gray-500">Year:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.publish_year || '—' }}</p>
                </div>
                <div>
                  <span class="text-gray-500">Edition:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.edition || '—' }}</p>
                </div>
                <div>
                  <span class="text-gray-500">Category:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.category?.name || '—' }}</p>
                </div>
                <div>
                  <span class="text-gray-500">Location:</span>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.location || '—' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="book.description" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Description</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ book.description }}</p>
          </div>
        </div>
      </div>

      <!-- Availability -->
      <div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Availability</h3>
          <div class="text-center py-4">
            <p class="text-4xl font-bold" :class="book.available_copies > 0 ? 'text-green-600' : 'text-red-600'">
              {{ book.available_copies }}
            </p>
            <p class="text-gray-500">of {{ book.total_copies }} copies available</p>
          </div>

          <div class="flex gap-2 mt-4">
            <button v-if="book.available_copies > 0"
               class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors">
              <ClockIcon class="w-4 h-4" />
              Loan
            </button>
            <button v-else
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-lg font-medium cursor-not-allowed" disabled>
              <BookmarkIcon class="w-4 h-4" />
              Reserve
            </button>
          </div>
        </div>

        <!-- Loan History -->
        <div v-if="book.loans?.length" class="mt-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Loans</h3>
          <div class="space-y-3">
            <div v-for="loan in book.loans.slice(0, 5)" :key="loan.id"
              class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">{{ loan.user?.name }}</span>
              <span class="text-gray-500">{{ formatDate(loan.loan_date) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>
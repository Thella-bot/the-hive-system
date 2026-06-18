<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import {
  BookOpenIcon,
  UserIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  BookmarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, required: true },
  recentLoans: { type: Array, default: () => [] },
  popularBooks: { type: Array, default: () => [] },
});

const statCards = [
  { name: 'Total Books', value: 'total_books', icon: BookOpenIcon, color: 'bg-blue-500' },
  { name: 'Available', value: 'available_books', icon: BookOpenIcon, color: 'bg-green-500' },
  { name: 'Active Loans', value: 'active_loans', icon: ClockIcon, color: 'bg-amber-500' },
  { name: 'Overdue', value: 'overdue_loans', icon: ExclamationTriangleIcon, color: 'bg-red-500' },
  { name: 'Reservations', value: 'pending_reservations', icon: BookmarkIcon, color: 'bg-purple-500' },
  { name: 'Categories', value: 'categories', icon: BookOpenIcon, color: 'bg-gray-500' },
];

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-ZA');
};
</script>

<template>
  <HiveLayout title="Library Dashboard" description="Library overview and statistics">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div v-for="stat in statCards" :key="stat.name"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center gap-4">
          <div :class="stat.color" class="p-3 rounded-lg text-white">
            <component :is="stat.icon" class="w-6 h-6" />
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.name }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats[stat.value] }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Loans -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Loans</h3>
        <div v-if="recentLoans.length === 0" class="text-center py-8 text-gray-500">
          No recent loans
        </div>
        <div v-else class="space-y-3">
          <div v-for="loan in recentLoans" :key="loan.id"
            class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ loan.book?.title }}</p>
              <p class="text-sm text-gray-500">{{ loan.user?.name }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">{{ formatDate(loan.loan_date) }}</p>
              <span :class="loan.status === 'overdue' ? 'text-red-600' : 'text-gray-500'"
                class="text-xs capitalize">{{ loan.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Popular Books -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Popular Books</h3>
        <div v-if="popularBooks.length === 0" class="text-center py-8 text-gray-500">
          No data yet
        </div>
        <div v-else class="space-y-3">
          <div v-for="book in popularBooks" :key="book.id"
            class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div>
              <p class="font-medium text-gray-900 dark:text-gray-100">{{ book.title }}</p>
              <p class="text-sm text-gray-500">{{ book.author }}</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold text-amber-600">{{ book.loans_count }}</p>
              <p class="text-xs text-gray-500">loans</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
      <Link :href="route('library.books.index')"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-amber-500 transition-colors">
        <BookOpenIcon class="w-8 h-8 text-amber-600 mb-2" />
        <p class="font-medium text-gray-900 dark:text-gray-100">Browse Books</p>
        <p class="text-sm text-gray-500">Search and reserve</p>
      </Link>
      <Link :href="route('library.loans.index')"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-amber-500 transition-colors">
        <ClockIcon class="w-8 h-8 text-amber-600 mb-2" />
        <p class="font-medium text-gray-900 dark:text-gray-100">My Loans</p>
        <p class="text-sm text-gray-500">View borrowed books</p>
      </Link>
      <Link :href="route('library.reservations.index')"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-amber-500 transition-colors">
        <BookmarkIcon class="w-8 h-8 text-amber-600 mb-2" />
        <p class="font-medium text-gray-900 dark:text-gray-100">Reservations</p>
        <p class="text-sm text-gray-500">My reserved books</p>
      </Link>
    </div>
  </HiveLayout>
</template>
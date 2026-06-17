<template>
  <form @submit.prevent="submit" class="relative w-full max-w-sm">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
      </svg>
    </div>
    <input
      v-model="form.query"
      type="search"
      name="search"
      id="search"
      class="block w-full rounded-lg border-gray-200 pl-10 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 bg-white"
      placeholder="Search..."
    />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  query: '',
});

const submit = () => {
  form.get(route('search.index'), {
    preserveState: true,
  });
};
</script>
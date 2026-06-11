<template>
  <HiveLayout :title="`Edit: ${year.name}`" description="Update academic year details">
    <template #header-actions>
      <Link :href="route('hive.academic-years.index')"
        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
        title="Academic Years">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </Link>
    </template>

    <div class="max-w-lg">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <div class="p-6 space-y-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Year Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label>
              <input v-model="form.start_date" type="date"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">End Date</label>
              <input v-model="form.end_date" type="date"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
            </div>
          </div>

          <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <label class="flex items-center gap-3 cursor-pointer">
              <div class="relative">
                <input v-model="form.is_current" type="checkbox" class="sr-only" />
                <div class="w-10 h-6 rounded-full transition-colors" :class="form.is_current ? 'bg-amber-500' : 'bg-gray-300'"></div>
                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                  :class="form.is_current ? 'translate-x-4' : 'translate-x-0'"></div>
              </div>
              <span class="text-sm font-medium text-gray-700">Current academic year</span>
            </label>
          </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 rounded-b-xl">
          <Link :href="route('hive.academic-years.index')"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

const props = defineProps({
  year: { type: Object, required: true },
})

const form = useForm({
  name:       props.year.name,
  start_date: props.year.start_date,
  end_date:   props.year.end_date,
  is_current: props.year.is_current,
})

const submit = () => form.put(route('hive.academic-years.update', { academic_year: props.year.id }))
</script>
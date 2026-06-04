<template>
  <HiveLayout title="New Cohort" description="Create a new student cohort">
    <template #header-actions>
      <Link :href="route('hive.cohorts.index')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
        ← Cohorts
      </Link>
    </template>

    <div class="max-w-lg">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <div class="p-6 space-y-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cohort Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" placeholder="e.g. Pastry Arts — Intake 2024A"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department <span class="text-red-500">*</span></label>
            <select v-model="form.department_id"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              :class="{ 'border-red-400': form.errors.department_id }">
              <option :value="null">— Select Department —</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <p v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Academic Year <span class="text-red-500">*</span></label>
            <select v-model="form.academic_year_id"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              :class="{ 'border-red-400': form.errors.academic_year_id }">
              <option :value="null">— Select Academic Year —</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">
                {{ y.name }}{{ y.is_current ? ' (Current)' : '' }}
              </option>
            </select>
            <p v-if="form.errors.academic_year_id" class="text-red-500 text-xs mt-1">{{ form.errors.academic_year_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Maximum Students <span class="text-red-500">*</span></label>
            <input v-model.number="form.max_students" type="number" min="1" max="200"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.max_students }" />
            <p class="text-xs text-gray-400 mt-1">Typically 15–25 students for kitchen-based learning.</p>
            <p v-if="form.errors.max_students" class="text-red-500 text-xs mt-1">{{ form.errors.max_students }}</p>
          </div>

          <label class="flex items-center gap-3 cursor-pointer">
            <div class="relative">
              <input v-model="form.is_active" type="checkbox" class="sr-only" />
              <div class="w-10 h-6 rounded-full transition-colors" :class="form.is_active ? 'bg-amber-500' : 'bg-gray-300'"></div>
              <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"></div>
            </div>
            <span class="text-sm font-medium text-gray-700">Active cohort</span>
          </label>

        </div>

        <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 rounded-b-xl">
          <Link :href="route('hive.cohorts.index')"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
            Create Cohort
          </button>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

defineProps({
  departments:   { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
})

const form = useForm({
  name:             '',
  department_id:    null,
  academic_year_id: null,
  max_students:     20,
  is_active:        true,
})

const submit = () => form.post(route('hive.cohorts.store'))
</script>
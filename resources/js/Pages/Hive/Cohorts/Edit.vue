<template>
  <HiveLayout :title="`Edit: ${cohort.name}`" description="Update cohort details">
    <template #header-actions>
      <Link :href="route('hive.cohorts.show', { cohort: cohort.id })" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
        ← Back to Cohort
      </Link>
    </template>

    <div class="max-w-lg">
      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <div class="p-6 space-y-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cohort Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department <span class="text-red-500">*</span></label>
            <select v-model="form.department_id"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Academic Year <span class="text-red-500">*</span></label>
            <select v-model="form.academic_year_id"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white">
              <option v-for="y in academicYears" :key="y.id" :value="y.id">
                {{ y.name }}{{ y.is_current ? ' (Current)' : '' }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Maximum Students</label>
            <input v-model.number="form.max_students" type="number" min="1" max="200"
              class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" />
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

        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between rounded-b-xl">
          <button v-if="canDelete" type="button" @click="confirmDelete = true"
            class="text-sm text-red-500 hover:text-red-700 font-medium">Delete cohort</button>
          <span v-else class="text-xs text-gray-400">Cannot delete — has enrolled students.</span>
          <div class="flex gap-3">
            <Link :href="route('hive.cohorts.show', { cohort: cohort.id })"
              class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</Link>
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
              Save Changes
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Delete modal -->
    <div v-if="confirmDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Cohort?</h3>
        <p class="text-sm text-gray-500 mb-6">
          This will permanently delete <strong>{{ cohort.name }}</strong>.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="confirmDelete = false"
            class="px-4 py-2 text-sm text-gray-600 font-medium rounded-lg hover:bg-gray-100">Cancel</button>
          <Link :href="route('hive.cohorts.destroy', { cohort: cohort.id })" method="delete" as="button"
            class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg">Delete</Link>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'

const props = defineProps({
  cohort:        { type: Object, required: true },
  departments:   { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
})

const confirmDelete = ref(false)
const canDelete = computed(() => !props.cohort.students?.length)

const form = useForm({
  name:             props.cohort.name,
  department_id:    props.cohort.department_id,
  academic_year_id: props.cohort.academic_year_id,
  max_students:     props.cohort.max_students,
  is_active:        props.cohort.is_active,
})

const submit = () => form.put(route('hive.cohorts.update', { cohort: props.cohort.id }))
</script>
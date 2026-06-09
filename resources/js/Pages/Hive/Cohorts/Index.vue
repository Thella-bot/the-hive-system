<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import HiveLayout from '@/Layouts/HiveLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import { useUser } from '@/composables/useUser';

const props = defineProps({
  cohorts:       { type: Object, required: true },
  departments:   { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
})

const { isAdmin } = useUser();

const filters = ref({ department_id: '', academic_year_id: '' })
const hasActiveFilters = computed(() => filters.value.department_id || filters.value.academic_year_id)

const applyFilters = () => router.get(route('hive.cohorts.index'), filters.value, { preserveState: true, replace: true })
const clearFilters = () => { filters.value = { department_id: '', academic_year_id: '' }; applyFilters() }

const capacityPct = (c) => Math.min(100, Math.round((c.students_count / c.max_students) * 100))
const capacityColor = (c) => {
  const pct = capacityPct(c)
  if (pct >= 90) return 'bg-red-500'
  if (pct >= 70) return 'bg-amber-500'
  return 'bg-green-500'
}
</script>

<template>
  <HiveLayout title="Cohorts" description="Student groups by department and academic year">
    <template #header-actions>
      <Link v-if="isAdmin" :href="route('hive.cohorts.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Cohort
      </Link>
    </template>

    <!-- Filters -->
    <div class="flex items-center gap-3 mb-5 flex-wrap">
      <select v-model="filters.department_id" @change="applyFilters"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
        <option value="">All Departments</option>
        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
      </select>

      <select v-model="filters.academic_year_id" @change="applyFilters"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
        <option value="">All Academic Years</option>
        <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
      </select>

      <button v-if="hasActiveFilters" @click="clearFilters"
        class="text-sm text-gray-500 hover:text-gray-700 underline">Clear filters</button>
    </div>

    <!-- Grid -->
    <div v-if="cohorts.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
      <p class="text-gray-400 text-sm">No cohorts found.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div v-for="cohort in cohorts.data" :key="cohort.id"
        class="bg-white rounded-xl border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
        <div class="h-1" :style="{ backgroundColor: cohort.department?.color ?? '#f59e0b' }"></div>
        <div class="p-5">
          <div class="flex items-start justify-between gap-2 mb-3">
            <div>
              <h3 class="font-semibold text-gray-900">{{ cohort.name }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ cohort.department?.name }} · {{ cohort.academic_year?.name }}
              </p>
            </div>
            <Badge :color="cohort.is_active ? 'green' : 'gray'">
              {{ cohort.is_active ? 'Active' : 'Closed' }}
            </Badge>
          </div>

          <!-- Capacity bar -->
          <div class="mb-4">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>{{ cohort.students_count }} enrolled</span>
              <span>{{ cohort.max_students }} max</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all"
                :class="capacityColor(cohort)"
                :style="{ width: capacityPct(cohort) + '%' }">
              </div>
            </div>
          </div>

          <div class="flex gap-2 pt-3 border-t border-gray-100">
            <Link :href="route('hive.cohorts.show', { cohort: cohort.id })"
              class="flex-1 text-center py-1.5 text-sm text-amber-600 hover:text-amber-700 font-medium rounded-lg hover:bg-amber-50 transition-colors">
              View
            </Link>
            <Link v-if="canManage" :href="route('hive.cohorts.edit', { cohort: cohort.id })"
              class="flex-1 text-center py-1.5 text-sm text-gray-600 hover:text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
              Edit
            </Link>
          </div>
        </div>
      </div>
    </div>

    <Pagination v-if="cohorts.data.length > 0" :links="cohorts.links" :meta="cohorts.meta" />
  </HiveLayout>
</template>
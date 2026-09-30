<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    enrollmentGroups: Array,
    totalEnrollments: Number,
    moduleCount: Number,
    modules: Array,
    academicYears: Array,
    filters: Object,
    pendingRequestCount: Number,
});

/** Module ids whose student list is expanded. */
const open = ref([]);

const isOpen = (moduleId) => open.value.includes(moduleId);

const toggle = (moduleId) => {
    open.value = isOpen(moduleId)
        ? open.value.filter((id) => id !== moduleId)
        : [...open.value, moduleId];
};
</script>

<template>
    <Head title="Enrollment Management" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enrollment Management</h2>
                <div class="flex gap-2">
                    <a
                        :href="route('hive.enrollment.requests')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest"
                        :class="pendingRequestCount > 0 ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    >
                        Requests<span v-if="pendingRequestCount > 0">&nbsp;({{ pendingRequestCount }})</span>
                    </a>
                    <a
                        :href="route('hive.enrollment.bulk')"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Bulk Enroll
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Module</label>
                            <select
                                :value="filters.module_id"
                                @change="$inertia.get(route('hive.enrollment.admin.index'), { ...filters, module_id: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Modules</option>
                                <option v-for="mod in modules" :key="mod.id" :value="mod.id">
                                    {{ mod.code }} - {{ mod.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                            <select
                                :value="filters.academic_year"
                                @change="$inertia.get(route('hive.enrollment.admin.index'), { ...filters, academic_year: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Years</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.name">
                                    {{ year.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Semester</label>
                            <select
                                :value="filters.semester"
                                @change="$inertia.get(route('hive.enrollment.admin.index'), { ...filters, semester: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Enrollments grouped by module -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                    <p v-if="enrollmentGroups.length > 0" class="mb-4 text-sm text-gray-500">
                        {{ totalEnrollments }} enrollment{{ totalEnrollments === 1 ? '' : 's' }}
                        across {{ moduleCount }} module{{ moduleCount === 1 ? '' : 's' }}.
                        Select a module to see its students.
                    </p>
                    <div v-if="enrollmentGroups.length === 0" class="text-center text-gray-500 py-8">
                        No enrollments found.
                    </div>
                        <div v-else>
                            <ul class="divide-y divide-gray-200">
                                <li v-for="group in enrollmentGroups" :key="group.module_id">
                                    <button
                                        type="button"
                                        @click="toggle(group.module_id)"
                                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50"
                                    >
                                        <div class="flex items-center gap-3">
                                            <svg
                                                class="w-4 h-4 text-gray-400 transition-transform"
                                                :class="isOpen(group.module_id) ? 'rotate-90' : ''"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ group.code }} &mdash; {{ group.name }}
                                                </div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ group.count }} student{{ group.count === 1 ? '' : 's' }}
                                        </span>
                                    </button>

                                    <div v-if="isOpen(group.module_id)" class="bg-gray-50 px-6 py-2">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                                    <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                                                    <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                                                    <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <tr v-for="student in group.students" :key="student.enrollment_id">
                                                    <td class="py-2 text-sm text-gray-900">{{ student.name }}</td>
                                                    <td class="py-2 text-sm text-gray-500">{{ student.student_number }}</td>
                                                    <td class="py-2 text-sm text-gray-500">
                                                        {{ student.academic_year }} &middot; Sem {{ student.semester }}
                                                    </td>
                                                    <td class="py-2 text-right">
                                                        <button
                                                            @click="$inertia.delete(route('hive.enrollment.destroy', student.enrollment_id))"
                                                            class="text-red-600 hover:text-red-900 text-sm"
                                                        >
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

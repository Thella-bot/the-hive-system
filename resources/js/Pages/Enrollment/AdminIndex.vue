<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    enrollments: Object,
    modules: Array,
    academicYears: Array,
    filters: Object,
});
</script>

<template>
    <Head title="Enrollment Management" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enrollment Management</h2>
                <div class="flex gap-2">
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
                                @change="$inertia.get(route('hive.enrollment.index'), { ...filters, module_id: $event.target.value })"
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
                                @change="$inertia.get(route('hive.enrollment.index'), { ...filters, academic_year: $event.target.value })"
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
                                @change="$inertia.get(route('hive.enrollment.index'), { ...filters, semester: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Enrollments Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="enrollments.data.length === 0" class="text-center text-gray-500 py-8">
                            No enrollments found.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Module</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="enrollment in enrollments.data" :key="enrollment.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ enrollment.student?.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ enrollment.student?.profile?.student_number }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ enrollment.module?.code }}</div>
                                            <div class="text-sm text-gray-500">{{ enrollment.module?.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ enrollment.academic_year }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Semester {{ enrollment.semester }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="$inertia.delete(route('hive.enrollment.destroy', enrollment.id))"
                                                class="text-red-600 hover:text-red-900 text-sm"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

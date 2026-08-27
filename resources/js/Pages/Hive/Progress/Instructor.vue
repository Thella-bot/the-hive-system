<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    module: Object,
    students: Array,
    gradables: Array,
});
</script>

<template>
    <Head title="Student Progress" />
    <HiveLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Student Progress</h2>
            <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-800">{{ students.length }}</p>
                            <p class="text-sm text-gray-600">Students</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800">{{ gradables.length }}</p>
                            <p class="text-sm text-gray-600">Assessments</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800">
                                {{ students.length > 0 ? Math.round(students.reduce((sum, s) => sum + s.percent, 0) / students.length) : 0 }}%
                            </p>
                            <p class="text-sm text-gray-600">Average Progress</p>
                        </div>
                    </div>
                </div>

                <!-- Student Progress Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4">Student Progress</h3>
                        <div v-if="students.length === 0" class="text-center text-gray-500 py-8">
                            No students enrolled.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Completed
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in students" :key="item.student.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ item.student.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ item.student.email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                                                    <div
                                                        :class="{
                                                            'bg-red-500': item.percent < 30,
                                                            'bg-yellow-500': item.percent >= 30 && item.percent < 70,
                                                            'bg-green-500': item.percent >= 70,
                                                        }"
                                                        class="h-2 rounded-full"
                                                        :style="{ width: item.percent + '%' }"
                                                    ></div>
                                                </div>
                                                <span class="text-sm text-gray-600">{{ item.percent }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ item.completed }} / {{ item.total }}
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

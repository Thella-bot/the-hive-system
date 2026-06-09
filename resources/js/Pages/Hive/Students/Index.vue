<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({
    students: Array,
});

const { isAdmin } = useUser();

const form = useForm({});

const deleteStudent = (id) => {
    if (confirm('Are you sure you want to delete this student?')) {
        form.delete(route('hive.students.destroy', { student: id }));
    }
};
</script>

<template>
    <HiveLayout title="Students">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Email</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="student in students" :key="student.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ student.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ student.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('hive.students.edit', { student: student.id })" class="text-amber-600 hover:text-amber-700 dark:text-amber-400">Edit</Link>
                                    <button v-if="isAdmin" @click="deleteStudent(student.id)" class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
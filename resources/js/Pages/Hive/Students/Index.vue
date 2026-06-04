<script setup>
import { computed, Link, useForm, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    students: Array,
});

const page = usePage();
const isAdmin = computed(() => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.includes('super-admin') || roles.includes('school-admin');
});

const form = useForm({});

const deleteStudent = (id) => {
    if (confirm('Are you sure you want to delete this student?')) {
        form.delete(route('hive.students.destroy', { student: id }));
    }
};
</script>

<template>
    <HiveLayout title="Students">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="student in students" :key="student.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ student.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('hive.students.edit', { student: student.id })" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            <button v-if="isAdmin" @click="deleteStudent(student.id)" class="ml-2 text-red-600 hover:text-red-900">Delete</button>
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
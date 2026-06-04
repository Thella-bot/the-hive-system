<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    programmes: Object,
});

const page = usePage();
const form = useForm({});

const canManage = () => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.includes('super-admin') || roles.includes('school-admin');
};

const deleteProgramme = (id) => {
    if (confirm('Are you sure you want to delete this programme?')) {
        form.delete(route('hive.programmes.destroy', { programme: id }));
    }
};
</script>

<template>
    <HiveLayout title="Programmes" description="Manage all programmes and their courses">
        <div class="max-w-7xl mx-auto">
            <div v-if="canManage()" class="flex justify-end mb-4">
                <Link :href="route('hive.programmes.create')">
                    <PrimaryButton>Create Programme</PrimaryButton>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="programme in programmes.data" :key="programme.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ programme.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ programme.department ? programme.department.name : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ programme.duration }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route('hive.programmes.show', { programme: programme.id })" class="text-amber-600 hover:text-amber-900 mr-3">View</Link>
                                <Link v-if="canManage()" :href="route('hive.programmes.edit', { programme: programme.id })" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                <button v-if="canManage()" @click="deleteProgramme(programme.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!programmes.data.length">
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No programmes found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </HiveLayout>
</template>
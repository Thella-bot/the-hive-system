<template>
    <Head title="Work Placements" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Work Placements</h1>
                    <Link :href="route('placements.create')" class="btn btn-primary">
                        + New Placement
                    </Link>
                </div>
                <div class="p-6 border-t">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2 text-left">Student</th>
                                <th class="border p-2 text-left">Organisation</th>
                                <th class="border p-2 text-left">Duration</th>
                                <th class="border p-2 text-left">Type</th>
                                <th class="border p-2 text-left">Status</th>
                                <th class="border p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="placement in placements.data" :key="placement.id">
                                <td class="border p-2">{{ placement.student.name }}</td>
                                <td class="border p-2">{{ placement.organisation_name }}</td>
                                <td class="border p-2">{{ placement.duration }}</td>
                                <td class="border p-2">{{ placement.type }}</td>
                                <td class="border p-2">{{ placement.status || 'Pending' }}</td>
                                <td class="border p-2">
                                    <Link :href="route('placements.show', placement.id)" class="text-blue-600 hover:underline">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">
                    <Pagination :links="placements.links" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    placements: {
        type: Object,
        required: true,
    },
});
</script>

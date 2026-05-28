<script setup>
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    applications: Array,
});

const approve = (application) => {
    if (confirm('Are you sure you want to approve this application?')) {
        router.put(route('hive.applications.update', application.id), { status: 'approved' });
    }
};

const reject = (application) => {
    if (confirm('Are you sure you want to reject this application?')) {
        router.delete(route('hive.applications.destroy', application.id));
    }
};
</script>

<template>
    <HiveLayout title="Applications">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pending Applications
            </h2>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programme</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied On</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="application in applications" :key="application.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ application.name }}</div>
                                    <div class="text-sm text-gray-500">{{ application.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ application.programme?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ new Date(application.created_at).toLocaleDateString() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="approve(application)" class="text-indigo-600 hover:text-indigo-900 mr-4">Approve</button>
                                    <button @click="reject(application)" class="text-red-600 hover:text-red-900">Reject</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
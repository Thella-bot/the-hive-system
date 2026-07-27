<script setup>
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    applications: Array,
});

const approve = (application) => {
    if (confirm('Are you sure you want to approve this application?')) {
        router.put(route('hive.applications.update', { application: application.id }), { status: 'approved' });
    }
};

const reject = (application) => {
    if (confirm('Are you sure you want to reject this application?')) {
        router.delete(route('hive.applications.destroy', { application: application.id }));
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

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Programme</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Applied On</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="application in applications" :key="application.id" class="hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ application.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ application.email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600 dark:text-gray-400">{{ application.programme?.name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-500 dark:text-gray-400">{{ new Date(application.created_at).toLocaleDateString() }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="approve(application)" class="text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium text-xs">Approve</button>
                                        <button @click="reject(application)" class="text-red-600 hover:text-red-700 dark:text-red-400 font-medium text-xs">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
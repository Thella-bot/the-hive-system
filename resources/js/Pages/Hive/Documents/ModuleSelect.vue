<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const { isStudent } = useUser();

defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <HiveLayout
        title="Documents"
        description="Select a module to view documents"
    >
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Documents</h1>
            <p class="text-gray-600">Select a module to view available documents</p>
        </div>

        <!-- Module Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
                v-for="mod in modules"
                :key="mod.id"
                :href="route('hive.documents.index', { module_id: mod.id })"
                class="block p-6 bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-amber-500 transition-all"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {{ mod.code }}
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ mod.name }}</h3>
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                    {{ mod.description || 'No description available' }}
                </p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ mod.credits || 0 }} Credits
                    </span>
                    <span class="text-amber-600 font-medium">View Documents</span>
                </div>
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="modules.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No modules found</h3>
            <p class="text-gray-500">
                {{ isStudent ? 'You are not enrolled in any modules.' : 'No modules available to display.' }}
            </p>
        </div>
    </HiveLayout>
</template>
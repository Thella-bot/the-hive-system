<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Chat'
    },
    description: {
        type: String,
        default: 'Select a module to start chatting'
    }
});
</script>

<template>
    <HiveLayout :title="title" :description="description">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
                v-for="mod in modules"
                :key="mod.id"
                :href="route('hive.chat.show', { module: mod.id })"
                class="block p-6 bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-amber-500 transition-all"
            >
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ mod.name }}</h3>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {{ mod.code }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                    {{ mod.description || 'No description available' }}
                </p>
                <div class="flex items-center text-sm text-gray-500">
                    <span class="mr-4">
                        <strong>{{ mod.credits }}</strong> Credits
                    </span>
                    <span v-if="mod.programme">
                        {{ mod.programme.name }}
                    </span>
                </div>
                <div class="mt-4 flex items-center text-amber-600">
                    <span class="text-sm font-medium">Click to join chat</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </Link>
        </div>

        <div v-if="modules.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No modules available</h3>
            <p class="text-gray-500">You are not enrolled in any modules yet.</p>
        </div>
    </HiveLayout>
</template>
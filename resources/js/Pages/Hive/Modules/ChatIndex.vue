<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    moduleChannels: { type: Array, default: () => [] },
    generalChannel: { type: Object, default: null },
    deptChannels: { type: Object, default: null },
    title: { type: String, default: 'Chat' },
    description: { type: String, default: 'Select a channel to start chatting' },
});

const channelIcon = (type) => {
    switch (type) {
        case 'general': return '🌐';
        case 'department': return '🏢';
        case 'direct': return '💬';
        default: return '📚';
    }
};

const channelLink = (channel) => {
    if (channel.channel_type === 'module') {
        return route('hive.chat.module', { module: channel.channel_id });
    }
    return route('hive.chat.channel', { channel: channel.id });
};
</script>

<template>
    <HiveLayout :title="title" :description="description">
        <!-- General Staff Channel -->
        <div v-if="generalChannel" class="mb-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Staff Channels</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    :href="route('hive.chat.channel', { channel: generalChannel.id })"
                    class="block p-4 bg-amber-50 rounded-lg border border-amber-200 shadow-sm hover:shadow-md hover:border-amber-500 transition-all"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <span>🌐</span>
                        <h3 class="text-base font-semibold text-amber-900">{{ generalChannel.name }}</h3>
                    </div>
                    <p class="text-sm text-amber-700">All-staff general discussion</p>
                </Link>
            </div>
        </div>

        <!-- Department Channels -->
        <div v-if="deptChannels" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    :href="route('hive.chat.channel', { channel: deptChannels.id })"
                    class="block p-4 bg-blue-50 rounded-lg border border-blue-200 shadow-sm hover:shadow-md hover:border-blue-500 transition-all"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <span>🏢</span>
                        <h3 class="text-base font-semibold text-blue-900">{{ deptChannels.name }}</h3>
                    </div>
                    <p class="text-sm text-blue-700">Department discussion</p>
                </Link>
            </div>
        </div>

        <!-- Module Channels -->
        <div>
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Module Channels</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="mod in moduleChannels"
                    :key="mod.id"
                    :href="route('hive.chat.module', { module: mod.id })"
                    class="block p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-amber-500 transition-all"
                >
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-semibold text-gray-900">{{ mod.name }}</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ mod.code }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ mod.description || 'No description available' }}</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <span v-if="mod.programme">{{ mod.programme.name }}</span>
                    </div>
                </Link>
            </div>
        </div>

        <div v-if="moduleChannels.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No channels available</h3>
            <p class="text-gray-500">You are not enrolled in any modules yet.</p>
        </div>
    </HiveLayout>
</template>
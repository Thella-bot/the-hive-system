<script setup>
import { router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const props = defineProps({
    notifications: Object,
    unreadCount: { type: Number, default: 0 },
});

const markRead = (id) => {
    router.post(route('notifications.read', { notification: id }));
};

const markAllRead = () => {
    router.post(route('notifications.readAll'));
};

const formatDate = (date) => {
    if (!date) return '';
    return dayjs(date).fromNow();
};

const notificationIcon = (type) => {
    if (type?.includes('Application')) return '📋';
    if (type?.includes('Leave')) return '🏖️';
    if (type?.includes('Submission')) return '📤';
    if (type?.includes('Announcement')) return '📢';
    if (type?.includes('User')) return '👤';
    return '🔔';
};
</script>

<template>
    <HiveLayout title="Notifications" :description="`${unreadCount} unread`">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
                    <p v-if="unreadCount > 0" class="text-sm text-amber-600 mt-0.5">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="text-sm text-amber-600 hover:text-amber-700 font-medium px-3 py-1.5 rounded-lg hover:bg-amber-50 transition-colors">
                    Mark all as read
                </button>
            </div>

            <!-- Notifications list -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Empty state -->
                <div v-if="!notifications.data.length" class="px-6 py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🔔</span>
                    </div>
                    <p class="text-gray-500 font-medium">No notifications</p>
                    <p class="text-sm text-gray-400 mt-1">You're all caught up.</p>
                </div>

                <!-- List -->
                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-amber-50/50': !notification.read_at }">

                        <!-- Icon -->
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
                            {{ notificationIcon(notification.type) }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-gray-900 leading-snug">
                                    {{ notification.data?.message || notification.data?.title || 'Notification' }}
                                </p>
                                <span v-if="!notification.read_at" class="w-2 h-2 bg-amber-500 rounded-full flex-shrink-0 mt-1.5"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ formatDate(notification.created_at) }}</p>
                        </div>

                        <!-- Action -->
                        <button
                            v-if="!notification.read_at"
                            @click="markRead(notification.id)"
                            class="text-xs text-amber-600 hover:text-amber-700 font-medium whitespace-nowrap hover:bg-amber-50 px-2 py-1 rounded transition-colors">
                            Mark read
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="notifications.data.length > 0"
                :links="notifications.links"
                :meta="notifications.meta"
                class="mt-4" />
        </div>
    </HiveLayout>
</template>
<template>
  <IntranetLayout>
    <div class="flex justify-between mb-4">
      <h1 class="text-2xl font-bold">Notifications</h1>
      <button @click="markAllRead" class="text-sm bg-gray-200 px-3 py-1 rounded">Mark all as read</button>
    </div>
    <div class="space-y-2">
      <div v-for="notification in notifications" :key="notification.id"
           class="bg-white p-3 shadow rounded flex justify-between"
           :class="{'bg-blue-50': !notification.read_at}">
        <div>
          <p class="font-medium">{{ notification.data.message }}</p>
          <p class="text-xs text-gray-500">{{ new Date(notification.created_at).toLocaleString() }}</p>
        </div>
        <button v-if="!notification.read_at" @click="markRead(notification.id)"
                class="text-xs text-indigo-600">Read</button>
      </div>
    </div>
    <div v-if="notifications.length === 0" class="text-gray-500 mt-4">No notifications.</div>
  </IntranetLayout>
</template>
<script setup>
import { router } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

defineProps({ notifications: Array });

const markRead = (id) => router.post(route('notifications.read', id));
const markAllRead = () => router.post(route('notifications.readAll'));
</script>

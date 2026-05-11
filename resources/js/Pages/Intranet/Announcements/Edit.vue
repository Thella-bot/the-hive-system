<template>
  <IntranetLayout>
    <h1 class="text-2xl font-bold mb-4">Edit Announcement</h1>
    <form @submit.prevent="form.put(route('intranet.announcements.update', announcement.id))" class="max-w-lg space-y-4">
      <!-- same fields, v-model bound to form -->
    </form>
  </IntranetLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ announcement: Object });
const form = useForm({
  title: props.announcement.title,
  body: props.announcement.body,
  category: props.announcement.category,
  target_roles: props.announcement.target_roles ?? [],
  is_pinned: props.announcement.is_pinned,
  expires_at: props.announcement.expires_at ? new Date(props.announcement.expires_at).toISOString().slice(0,16) : '',
});
</script>
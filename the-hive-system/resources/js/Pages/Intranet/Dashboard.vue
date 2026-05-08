<template>
  <IntranetLayout>
    <div class="p-6">
      <h1 class="text-2xl font-semibold">Welcome, {{ user.name }}</h1>
      <p class="mt-2">Role: {{ user.roles.map(r => r.name).join(', ') }}</p>
    </div>
  </IntranetLayout>
</template>

<script setup>
import IntranetLayout from '@/Layouts/IntranetLayout.vue';
defineProps({ user: Object });
</script>
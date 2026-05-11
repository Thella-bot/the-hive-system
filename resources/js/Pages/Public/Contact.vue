<template>
  <PublicLayout>
    <h1 class="text-3xl font-bold">Contact Us</h1>
    <form @submit.prevent="form.post(route('contact.store'))" class="max-w-md mt-6 space-y-4">
      <div><input v-model="form.name" placeholder="Your Name" class="w-full border rounded p-2" required /></div>
      <div><input v-model="form.email" type="email" placeholder="Email" class="w-full border rounded p-2" required /></div>
      <div><textarea v-model="form.message" placeholder="Message" rows="5" class="w-full border rounded p-2" required></textarea></div>
      <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Send</button>
    </form>
  </PublicLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
const form = useForm({ name: '', email: '', message: '' });
</script>
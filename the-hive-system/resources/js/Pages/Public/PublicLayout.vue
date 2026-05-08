<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <nav class="bg-orange-700 text-white p-4">
      <div class="container mx-auto flex justify-between items-center">
        <Link :href="route('home')" class="text-2xl font-bold">Honey Bee Culinary Institute</Link>
        <div class="space-x-4">
          <Link :href="route('home')" class="hover:underline">Home</Link>
          <Link :href="route('about')" class="hover:underline">About</Link>
          <Link :href="route('programmes')" class="hover:underline">Programmes</Link>
          <Link :href="route('contact')" class="hover:underline">Contact</Link>
          <!-- Login/Register -->
          <span v-if="$page.props.user">
            <Link :href="route('intranet.dashboard')" class="hover:underline">Intranet</Link>
          </span>
          <span v-else>
            <Link :href="route('login')" class="hover:underline">Login</Link>
            <Link :href="route('register')" class="hover:underline">Register</Link>
          </span>
        </div>
      </div>
    </nav>
    <main class="flex-grow container mx-auto p-6">
      <slot />
    </main>
    <footer class="bg-gray-800 text-white p-4 text-center">
      &copy; {{ new Date().getFullYear() }} Honey Bee Culinary Institute. All rights reserved.
    </footer>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
</script>
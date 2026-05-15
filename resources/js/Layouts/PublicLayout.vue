<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const logoUrl = ref('/images/hbci-logo.png');
const cchplLogoUrl = ref('/images/cchpl-alt-logo.png');
const lcaLogoUrl = ref('/images/lca-logo.png');
</script>

<template>
  <div class="min-h-screen flex flex-col font-sans">
    <header class="bg-white shadow-md">
      <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <Link :href="route('home')">
          <img :src="logoUrl" alt="Honey Bee Culinary Institute" class="h-12" />
        </Link>
        <nav class="hidden md:flex space-x-8 items-center">
          <Link :href="route('home')" class="text-gray-600 hover:text-orange-500">Home</Link>
          <Link :href="route('about')" class="text-gray-600 hover:text-orange-500">About Us</Link>
          <Link :href="route('programmes')" class="text-gray-600 hover:text-orange-500">Programmes</Link>
          <Link :href="route('contact')" class="text-gray-600 hover:text-orange-500">Contact Us</Link>
          <a href="/login" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Login</a>
        </nav>
      </div>
    </header>

    <main class="flex-grow">
      <slot />
    </main>

    <footer class="bg-gray-800 text-white">
      <div class="container mx-auto px-6 py-8">
        <div class="grid md:grid-cols-3 gap-8">
          <div>
            <h3 class="text-lg font-semibold">Honey Bee Culinary Institute</h3>
            <p class="mt-2 text-gray-400">The International Culinary and Hospitality Academy of Lesotho.</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold">Contact Us</h3>
            <p class="mt-2 text-gray-400">Email: info@hbci.ac.ls</p>
            <p class="text-gray-400">Phone: +266 5888 1234</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold">Follow Us</h3>
            <div class="flex space-x-4 mt-2">
              <a href="#" class="text-gray-400 hover:text-white">Facebook</a>
              <a href="#" class="text-gray-400 hover:text-white">Instagram</a>
            </div>
          </div>
        </div>
        <div class="mt-8 border-t border-gray-700 pt-4 text-center text-gray-500">
          &copy; 2024 Honey Bee Culinary Institute. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>
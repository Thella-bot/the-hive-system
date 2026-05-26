<template>
  <PublicLayout>
    <div class="relative h-[70vh] bg-cover bg-center" style="background-image: url('/images/hero-background.jpg');">
      <div class="absolute inset-0 bg-black opacity-60"></div>
      <div class="relative z-10 flex flex-col items-center justify-center h-full text-white text-center px-6">
        <h1 class="text-4xl md:text-6xl font-bold leading-tight">The International Culinary and Hospitality Academy of Lesotho</h1>
        <p class="mt-4 text-lg md:text-xl max-w-2xl">Your journey to culinary and hospitality excellence starts here. We are dedicated to providing world-class training and education.</p>
        <Link :href="route('programmes')" class="mt-8 px-8 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition">Our Programmes</Link>
        
        <!-- Logos at the bottom -->
        <div class="absolute bottom-8 flex justify-center items-center space-x-8">
          <img src="/images/cchpl-alt-logo.png" alt="CCHPL Logo" class="h-16 opacity-80 hover:opacity-100 transition">
          <img src="/images/lca-logo.png" alt="LCA Logo" class="h-16 opacity-80 hover:opacity-100 transition">
        </div>
      </div>
    </div>

    <!-- About Us Section -->
    <div class="py-16 bg-white">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-amber-800">Welcome to Honey Bee Culinary Institute</h2>
            <p class="mt-4 text-gray-600">The Honey Bee Culinary Institute (HBCI) is a premier culinary and hospitality training center in Lesotho. We are committed to providing our students with the highest quality education and practical skills needed to succeed in the dynamic and exciting world of culinary arts and hospitality management.</p>
            <p class="mt-4 text-gray-600">Our state-of-the-art facilities, experienced instructors, and hands-on approach to learning ensure that our graduates are well-prepared for the challenges of the industry.</p>
            <Link :href="route('about')" class="mt-6 inline-block text-amber-600 font-semibold hover:underline">Learn More About Us</Link>
          </div>
          <div>
            <img src="/images/about-us-image.jpg" alt="Our Team" class="rounded-lg shadow-lg">
          </div>
        </div>
      </div>
    </div>

    <!-- Our Programmes Section -->
    <div class="py-16 bg-gray-50">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center text-amber-800">Explore Our Programmes</h2>
        <div class="grid md:grid-cols-3 gap-8 mt-8">
          <div v-for="programme in programmes" :key="programme.id" class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition">
            <img :src="programme.image_url || '/images/programme-placeholder.jpg'" :alt="programme.name" class="w-full h-48 object-cover">
            <div class="p-6">
              <h3 class="text-xl font-semibold h-16">{{ programme.name }}</h3>
              <p class="mt-2 text-gray-600">{{ programme.duration }}</p>
              <Link :href="route('programmes')" class="mt-4 inline-block text-amber-600 font-semibold hover:underline">View Details</Link>
            </div>
          </div>
        </div>
        <div class="text-center mt-8">
          <Link :href="route('programmes')" class="px-8 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition">View All Programmes</Link>
        </div>
      </div>
    </div>

    <!-- Testimonials Section -->
    <div class="py-16 bg-white">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center text-amber-800">What Our Students Say</h2>
        <div class="grid md:grid-cols-2 gap-8 mt-8">
          <div class="bg-gray-100 p-6 rounded-lg">
            <p class="text-gray-600">"The hands-on training at HBCI is second to none. I feel confident and prepared for my career as a chef."</p>
            <p class="mt-4 font-semibold">- Lerato M.</p>
          </div>
          <div class="bg-gray-100 p-6 rounded-lg">
            <p class="text-gray-600">"The instructors are amazing! They are so knowledgeable and supportive. I've learned so much during my time here."</p>
            <p class="mt-4 font-semibold">- Thabo K.</p>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
<script setup>
import PublicLayout from './PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
defineProps({ programmes: Array, announcements: Array });
</script>
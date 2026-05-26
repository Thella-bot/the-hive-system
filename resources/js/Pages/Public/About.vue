<template>
  <PublicLayout>
    <!-- Page Header -->
    <div class="bg-gray-100 py-12">
      <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-amber-800">About Us</h1>
        <p class="mt-2 text-lg text-gray-600">Learn more about our mission, vision, and commitment to excellence.</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-16 bg-white">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-amber-800">Our Story</h2>
            <p class="mt-4 text-gray-600">The Honey Bee Culinary Institute (HBCI) was founded with a passion for culinary arts and a commitment to providing world-class training in Lesotho. We believe in the power of education to transform lives and are dedicated to nurturing the next generation of culinary and hospitality leaders.</p>
            <p class="mt-4 text-gray-600">Our curriculum is designed to be comprehensive, combining theoretical knowledge with intensive hands-on practice. We aim to foster creativity, discipline, and a deep respect for the culinary craft.</p>
          </div>
          <div>
            <img src="/images/about-story.jpg" alt="Our Kitchen" class="rounded-lg shadow-lg">
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center mt-16">
          <div class="md:order-2">
            <h2 class="text-3xl font-bold text-amber-800">Our Mission & Vision</h2>
            <div class="mt-4">
              <h3 class="text-xl font-semibold">Mission</h3>
              <p class="mt-2 text-gray-600">To provide an inspiring and innovative learning environment that equips students with the skills, knowledge, and professionalism required to excel in the global culinary and hospitality industry.</p>
            </div>
            <div class="mt-6">
              <h3 class="text-xl font-semibold">Vision</h3>
              <p class="mt-2 text-gray-600">To be the leading culinary and hospitality academy in Southern Africa, recognized for our excellence in training, commitment to student success, and contribution to the industry.</p>
            </div>
          </div>
          <div class="md:order-1">
            <img src="/images/about-mission.jpg" alt="Our Students" class="rounded-lg shadow-lg">
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from './PublicLayout.vue';
</script>
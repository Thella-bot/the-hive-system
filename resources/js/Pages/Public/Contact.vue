<template>
  <PublicLayout>
    <!-- Page Header -->
    <div class="bg-gray-100 py-12">
      <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold text-amber-800">Contact Us</h1>
        <p class="mt-2 text-lg text-gray-600">We'd love to hear from you. Get in touch with us today.</p>
      </div>
    </div>

    <!-- Contact Section -->
    <div class="py-16 bg-white">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12">
          <!-- Contact Form -->
          <div>
            <h2 class="text-2xl font-bold text-amber-800">Send Us a Message</h2>
            <form @submit.prevent="submit" class="mt-6">
              <div v-if="form.wasSuccessful" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"> Your message has been sent.</span>
              </div>
              <div class="space-y-4">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                  <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                  <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input v-model="form.email" type="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                  <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                  <textarea v-model="form.message" id="message" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500"></textarea>
                  <div v-if="form.errors.message" class="text-red-600 text-sm mt-1">{{ form.errors.message }}</div>
                </div>
              </div>
              <div class="mt-6">
                <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
                  Send Message
                </button>
              </div>
            </form>
          </div>

          <!-- Contact Info & Map -->
          <div>
            <h2 class="text-2xl font-bold text-amber-800">Contact Information</h2>
            <div class="mt-6 space-y-4 text-gray-600">
              <p><strong>Address:</strong> Maseru, Lesotho</p>
              <p><strong>Email:</strong> info@hbci.co.ls</p>
              <p><strong>Phone:</strong> +266 5888 1234</p>
            </div>
            <div class="mt-8">
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3477.39562153942!2d27.49487031510691!3d-29.3293159821372!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1e8defc3a5a43e7d%3A0x83b42f391623f4d!2sMaseru%2C%20Lesotho!5e0!3m2!1sen!2sus!4v1620831333333!5m2!1sen!2sus"
                width="100%"
                height="300"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
              </iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from './PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  message: '',
});

const submit = () => {
  form.post(route('contact.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
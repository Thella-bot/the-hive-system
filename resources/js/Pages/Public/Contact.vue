<template>
  <PublicLayout>
    <!-- Page Hero -->
    <section class="bg-gradient-to-br from-hbci-warm-800 via-hbci-warm-700 to-hbci-warm-900 text-white py-20 lg:py-28">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold">Contact Us</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Have questions about our programmes or admissions? We'd love to hear from you.</p>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">
          <!-- Form (3 cols) -->
          <div class="lg:col-span-3">
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-8 lg:p-10">
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Send Us a Message</h2>
              <p class="text-gray-500 text-sm mb-8">Fill in the form below and we'll get back to you within 2 business days.</p>

              <!-- Success Message -->
              <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <div class="flex items-center gap-3 text-emerald-700">
                  <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
              </div>

              <form @submit.prevent="submit" class="space-y-5">
                <div class="grid sm:grid-cols-2 gap-5">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                    <input v-model="form.name" type="text" id="name" required
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      :class="form.errors.name ? 'border-red-400' : ''" />
                    <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                    <input v-model="form.email" type="email" id="email" required
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                      :class="form.errors.email ? 'border-red-400' : ''" />
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                  </div>
                </div>
                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                  <select v-model="form.subject" id="subject"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    <option value="">Select a topic</option>
                    <option value="admissions">Admissions & Applications</option>
                    <option value="programmes">Programme Information</option>
                    <option value="fees">Fees & Scholarships</option>
                    <option value="facilities">Campus & Facilities</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 mb-1.5">Message *</label>
                  <textarea v-model="form.message" id="message" rows="6" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition resize-none"
                    :class="form.errors.message ? 'border-red-400' : ''"
                    placeholder="Tell us how we can help you..."></textarea>
                  <div v-if="form.errors.message" class="text-red-600 text-xs mt-1">{{ form.errors.message }}</div>
                </div>
                <div>
                  <button type="submit" :disabled="form.processing"
                    class="w-full sm:w-auto px-8 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition disabled:opacity-50 flex items-center justify-center gap-2">
                    <span v-if="form.processing">Sending...</span>
                    <span v-else>Send Message</span>
                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Info (2 cols) -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Contact Cards -->
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 space-y-5">
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">Visit Us</h3>
                  <p class="text-gray-600 text-sm mt-1">Maseru, Lesotho</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">Email Us</h3>
                  <p class="text-gray-600 text-sm mt-1">info@hbci.co.ls</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">Call Us</h3>
                  <p class="text-gray-600 text-sm mt-1">+266 5888 1234</p>
                </div>
              </div>
            </div>

            <!-- Office Hours -->
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6">
              <h3 class="font-semibold text-gray-900 mb-4">Office Hours</h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Monday — Friday</span>
                  <span class="text-gray-900 font-medium">8:00 AM — 4:30 PM</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Saturday</span>
                  <span class="text-gray-900 font-medium">9:00 AM — 1:00 PM</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Sunday</span>
                  <span class="text-gray-400">Closed</span>
                </div>
              </div>
            </div>

            <!-- Social -->
            <div>
              <h3 class="font-semibold text-gray-900 mb-3">Follow Us</h3>
              <div class="flex gap-3">
                <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-amber-100 hover:text-amber-600 transition">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-amber-100 hover:text-amber-600 transition">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
});

const submit = () => {
  form.post(route('contact.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
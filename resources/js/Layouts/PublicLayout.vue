<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import SocialIcons from '@/Components/SocialIcons.vue';

const mobileMenuOpen = ref(false);
const currentYear = new Date().getFullYear();

const navLinks = [
  { name: 'Home', href: route('home') },
  { name: 'About', href: route('about') },
  { name: 'Programmes', href: route('programmes') },
  { name: 'Contact', href: route('contact') },
];

const isActive = (href) => {
  try {
    return window.location.pathname === new URL(href, window.location.origin).pathname;
  } catch {
    return false;
  }
};
</script>

<template>
  <div class="min-h-screen flex flex-col font-sans bg-white dark:bg-gray-900">
    <!-- Sticky Modern Header -->
    <header class="bg-white sticky top-0 z-50 shadow-sm border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 lg:h-20">
          <!-- Logo -->
          <Link :href="route('home')" class="flex items-center gap-3">
            <img src="/images/hbci-logo.png" alt="Honey Bee Culinary Institute" class="h-10 lg:h-12 w-auto" />
          </Link>

          <!-- Desktop Nav -->
          <nav class="hidden lg:flex items-center gap-1">
            <Link
              v-for="link in navLinks"
              :key="link.name"
              :href="link.href"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="isActive(link.href)
                ? 'text-amber-700 bg-amber-50'
                : 'text-gray-600 hover:text-amber-600 hover:bg-gray-50'"
            >
              {{ link.name }}
            </Link>
          </nav>

          <!-- CTA + Desktop Login -->
          <div class="hidden lg:flex items-center gap-3">
            <ThemeToggle />
            <Link
              :href="route('apply')"
              class="px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition shadow-sm"
            >
              Apply Now
            </Link>
            <Link
              href="/login"
              class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-amber-600 transition"
            >
              Login
            </Link>
          </div>

          <!-- Mobile Menu Button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-amber-600 hover:bg-gray-100 transition"
          >
            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-show="mobileMenuOpen" class="lg:hidden bg-white border-t border-gray-100">
        <div class="px-4 py-4 space-y-1">
          <Link
            v-for="link in navLinks"
            :key="link.name"
            :href="link.href"
            @click="mobileMenuOpen = false"
            class="block px-4 py-3 rounded-lg text-sm font-medium transition-colors"
            :class="isActive(link.href)
              ? 'text-amber-700 bg-amber-50'
              : 'text-gray-600 hover:text-amber-600 hover:bg-gray-50'"
          >
            {{ link.name }}
          </Link>
          <div class="pt-4 flex flex-col gap-3">
            <Link
              :href="route('apply')"
              @click="mobileMenuOpen = false"
              class="block text-center px-5 py-3 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition"
            >
              Apply Now
            </Link>
            <Link
              href="/login"
              @click="mobileMenuOpen = false"
              class="block text-center px-5 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition"
            >
              Login to Portal
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
          <!-- Brand Column -->
          <div class="lg:col-span-2">
            <Link :href="route('home')" class="inline-block">
              <img src="/images/hbci-logo.png" alt="HBCI" class="h-12 w-auto" />
            </Link>
            <p class="mt-4 text-gray-400 text-sm max-w-md leading-relaxed">
              The International Culinary and Hospitality Academy of Lesotho. Empowering the next generation of culinary and hospitality professionals through world-class training.
            </p>
            <div class="mt-6">
              <SocialIcons />
            </div>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Quick Links</h3>
            <ul class="mt-4 space-y-3">
              <li><Link :href="route('about')" class="text-gray-400 hover:text-amber-400 transition text-sm">About Us</Link></li>
              <li><Link :href="route('programmes')" class="text-gray-400 hover:text-amber-400 transition text-sm">Programmes</Link></li>
              <li><Link :href="route('apply')" class="text-gray-400 hover:text-amber-400 transition text-sm">Admissions</Link></li>
              <li><Link :href="route('contact')" class="text-gray-400 hover:text-amber-400 transition text-sm">Contact</Link></li>
              <li><Link href="/login" class="text-gray-400 hover:text-amber-400 transition text-sm">Student Portal</Link></li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Contact</h3>
            <ul class="mt-4 space-y-3 text-sm text-gray-400">
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Maseru, Lesotho
              </li>
              <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                info@hbci.co.ls
              </li>
              <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                +266 5888 1234
              </li>
            </ul>
          </div>
        </div>

        <!-- Partnership Logos -->
        <div class="mt-12 pt-8 border-t border-gray-800">
          <p class="text-xs text-gray-400 text-center mb-6">Accredited & Affiliated With</p>
          <div class="flex flex-wrap items-center justify-center gap-6">
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/cchpl-logo.png" alt="CCHPL" class="h-16 w-auto" />
            </div>
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/lca-logo.png" alt="LCA" class="h-14 w-auto" />
            </div>
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/culinary-institute-logo.png" alt="City & Guilds" class="h-14 w-auto" />
            </div>
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/city-n-guilds-logo.png" alt="City & Guilds" class="h-14 w-auto" />
            </div>
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/sachef-logo.png" alt="SaChef" class="h-14 w-auto" />
            </div>
            <div class="bg-white rounded-lg p-3 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
              <img src="/images/safa-logo.png" alt="SAFA" class="h-14 w-auto" />
            </div>
          </div>
        </div>

        <!-- Copyright -->
        <div class="mt-8 pt-8 border-t border-gray-800 text-center">
          <p class="text-sm text-gray-500">
            &copy; {{ currentYear }} Honey Bee Culinary Institute. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
<template>
  <PublicLayout>
    <!-- Page Hero -->
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-20 lg:py-28 overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-amber-600/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-400/5 rounded-full blur-3xl"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-600/20 border border-amber-500/30 rounded-full text-amber-400 text-sm font-medium mb-6">
          <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
          Est. 2018
        </span>
        <h1 class="text-4xl lg:text-5xl font-bold">About Honey Bee Culinary Institute</h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Empowering the next generation of culinary and hospitality professionals since establishment.</p>
      </div>
    </section>

    <!-- Our Story -->
    <section class="py-20 bg-white dark:bg-gray-900 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          <div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-full text-sm font-medium mb-6">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              Our Story
            </span>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-6">Building Future Culinary Professionals</h2>
            <div class="space-y-4 text-gray-600 dark:text-gray-400 leading-relaxed">
              <p>The Honey Bee Culinary Institute (HBCI) was founded with a passion for culinary arts and a commitment to providing world-class training right here in Lesotho. We saw a need for professional culinary education that would open doors for young Basotho people to pursue careers locally and abroad.</p>
              <p>Our curriculum combines comprehensive theoretical knowledge with intensive hands-on practice in fully equipped professional kitchens. We aim to foster creativity, discipline, and a deep respect for the culinary craft alongside the business skills needed in today's hospitality industry.</p>
              <p>Today, HBCI stands as Lesotho's premier culinary and hospitality academy, producing graduates who work in kitchens and hotels across Southern Africa and beyond.</p>
            </div>
          </div>
          <div class="relative">
            <div class="absolute -top-4 -right-4 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
            <div class="relative bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-900/40 dark:to-amber-900/20 rounded-2xl p-8 lg:p-12 border border-amber-200 dark:border-amber-700">
              <div class="grid grid-cols-2 gap-6">
                <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl">
                  <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                  </div>
                  <p class="text-3xl lg:text-4xl font-bold text-amber-700 dark:text-amber-400">500+</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Graduates</p>
                </div>
                <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl">
                  <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                  </div>
                  <p class="text-3xl lg:text-4xl font-bold text-amber-700 dark:text-amber-400">15+</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Instructors</p>
                </div>
                <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl">
                  <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  </div>
                  <p class="text-3xl lg:text-4xl font-bold text-amber-700 dark:text-amber-400">6</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Programmes</p>
                </div>
                <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-xl">
                  <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  </div>
                  <p class="text-3xl lg:text-4xl font-bold text-amber-700 dark:text-amber-400">95%</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Placed</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
          <!-- Mission -->
          <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 lg:p-10 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Our Mission</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">To provide an inspiring and innovative learning environment that equips students with the skills, knowledge, and professionalism required to excel in the global culinary and hospitality industry. We are committed to transforming lives through education and opening pathways to meaningful careers.</p>
          </div>
          <!-- Vision -->
          <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 lg:p-10 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Our Vision</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">To be the leading culinary and hospitality academy in Southern Africa, recognized for excellence in training, commitment to student success, and meaningful contribution to the industry. We envision our graduates as ambassadors of quality who elevate the culinary landscape wherever their careers take them.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-full text-sm font-medium mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            Core Values
          </span>
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">What Drives Us</h2>
          <p class="mt-3 text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">These values shape everything we do at HBCI.</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="group bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Excellence</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Setting the highest standards in everything we do.</p>
          </div>
          <div class="group bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Learning</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Continuous growth through knowledge and practice.</p>
          </div>
          <div class="group bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Community</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Building relationships that support growth.</p>
          </div>
          <div class="group bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Passion</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Love for food, hospitality, and teaching.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Accreditations & Partnerships -->
    <section class="py-20 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">Accreditations & Partnerships</h2>
          <p class="mt-4 text-gray-600 dark:text-gray-400">Our programmes are backed by leading international bodies.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/city-n-guilds-logo.png" alt="City & Guilds" class="h-12 w-auto object-contain" />
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/lca-logo.png" alt="Luxury Hospitality Academy" class="h-12 w-auto object-contain" />
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/safa-logo.png" alt="SAFA" class="h-12 w-auto object-contain" />
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/sachef-logo.png" alt="SAChef" class="h-12 w-auto object-contain" />
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/cchpl-logo.png" alt="CCHPL" class="h-12 w-auto object-contain" />
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 flex items-center justify-center border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <img src="/images/culinary-institute-logo.png" alt="Culinary Institute" class="h-12 w-auto object-contain" />
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-br from-amber-600 to-amber-700 relative overflow-hidden">
      <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-72 h-72 bg-white/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl lg:text-3xl font-bold text-white">Join Us on Your Culinary Journey</h2>
        <p class="mt-3 text-amber-100">Applications are open for the upcoming intake.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
          <Link :href="route('apply')" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-amber-700 font-bold rounded-lg hover:bg-amber-50 transition shadow-lg">
            Apply Now
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </Link>
          <Link :href="route('contact')" class="inline-flex items-center gap-2 px-8 py-3 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition">
            Contact Us
          </Link>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>
<template>
  <PublicLayout>
    <!-- Announcements Banner -->
    <section v-if="announcements && announcements.length" class="bg-amber-50 dark:bg-amber-900/30 border-b border-amber-200 dark:border-amber-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center gap-3">
          <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-amber-600 text-white text-xs font-bold rounded-full">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            NEW
          </span>
          <p class="text-sm text-amber-800 dark:text-amber-200 font-medium truncate">
            {{ announcements[0].title }}
          </p>
          <Link :href="route('about')" class="flex-shrink-0 text-xs text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-semibold underline">
            Learn more
          </Link>
        </div>
      </div>
    </section>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white overflow-hidden">
      <!-- Background layers -->
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-amber-600/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-400/5 rounded-full blur-3xl"></div>

      <!-- Decorative elements -->
      <div class="absolute top-20 left-10 w-20 h-20 border border-amber-500/20 rounded-full"></div>
      <div class="absolute bottom-32 right-20 w-12 h-12 border border-amber-500/10 rounded-full"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          <div class="space-y-8">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-600/20 border border-amber-500/30 rounded-full text-amber-400 text-sm font-medium">
              <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
              Lesotho's Premier Culinary Academy
            </div>
            <h1 class="text-4xl lg:text-5xl xl:text-6xl font-bold leading-tight">
              The International Culinary &amp; Hospitality<br>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-500">Academy of Lesotho</span>
            </h1>
            <p class="text-lg text-gray-300 max-w-lg leading-relaxed">
              Launch your career in culinary arts and hospitality management with world-class training, expert instructors, and internationally recognized certifications.
            </p>
            <div class="flex flex-wrap gap-4">
              <Link :href="route('apply')" class="group inline-flex items-center gap-2 px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition shadow-lg shadow-amber-600/30 hover:shadow-amber-600/50">
                Apply Now
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </Link>
              <Link :href="route('programmes')" class="group inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white font-semibold rounded-lg hover:bg-white/20 transition border border-white/20">
                Explore Programmes
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </Link>
            </div>
          </div>
          <div class="hidden lg:flex relative justify-center items-center">
            <div class="absolute w-80 h-80 bg-amber-500/20 rounded-full blur-3xl"></div>
            <div class="absolute w-64 h-64 border border-amber-500/10 rounded-full animate-pulse"></div>
            <div class="relative animate-float">
              <img src="/images/hbci-logo.png" alt="HBCI" class="w-full max-w-sm mx-auto opacity-90 drop-shadow-2xl" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Bar -->
    <section class="bg-gradient-to-r from-amber-600 to-amber-700 py-10 relative overflow-hidden">
      <div class="absolute inset-0 bg-white/5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
      <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center relative">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <p class="text-3xl lg:text-4xl font-bold text-white">500+</p>
            <p class="text-amber-200 text-sm mt-1">Graduates</p>
          </div>
          <div class="text-center relative">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
              </svg>
            </div>
            <p class="text-3xl lg:text-4xl font-bold text-white">15+</p>
            <p class="text-amber-200 text-sm mt-1">Expert Instructors</p>
          </div>
          <div class="text-center relative">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <p class="text-3xl lg:text-4xl font-bold text-white">6</p>
            <p class="text-amber-200 text-sm mt-1">Programmes</p>
          </div>
          <div class="text-center relative">
            <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <p class="text-3xl lg:text-4xl font-bold text-white">95%</p>
            <p class="text-amber-200 text-sm mt-1">Employment Rate</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-20 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">Why Choose HBCI?</h2>
          <p class="mt-4 text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">We combine hands-on training with industry expertise to prepare you for a successful career.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Internationally Accredited</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Earn certifications recognized globally by City & Guilds and Luxury Hospitality Academy.</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Hands-On Training</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Train in fully-equipped professional kitchens with real-world culinary experiences.</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.628-9-1.732-2.78 1.104-6.817 1.732-9 1.732-3.183 0-6.22-.628-9-1.732a23.942 23.942 0 010-4.49C2.78 8.778 6.817 8.15 9.9 7.046A23.99 23.99 0 0112 4c4.97 0 9 1.522 12 4.255A23.931 23.931 0 0112 15z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Hospitality Network</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Gain industry connections through our partnerships with leading hotels and restaurants.</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Expert Instructors</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Learn from industry professionals with years of culinary and hospitality experience.</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Career Support</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Get guidance with job placement assistance and career development resources.</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 transition">
            <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Study Local, Go Global</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Quality education right here in Lesotho with pathways to international opportunities.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Programmes -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
          <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium rounded-full mb-4">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              Accredited Programmes
            </span>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">Our Programmes</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Choose from a range of accredited culinary and hospitality programmes.</p>
          </div>
          <Link :href="route('programmes')" class="group inline-flex items-center gap-2 text-amber-600 dark:text-amber-400 font-semibold hover:text-amber-700 dark:hover:text-amber-300 transition">
            View all programmes
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </Link>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="programme in programmes" :key="programme.id" class="group bg-white dark:bg-gray-700 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-600 overflow-hidden hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-500 hover:-translate-y-1 transition-all duration-300">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-semibold rounded-full">{{ programme.duration }}</span>
                <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/40 rounded-lg flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                  <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-300 transition">{{ programme.name }}</h3>
              <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2">{{ programme.description }}</p>
            </div>
            <div class="px-6 pb-6">
              <Link :href="route('apply')" class="inline-flex items-center gap-2 text-amber-600 dark:text-amber-400 font-semibold text-sm hover:text-amber-700 dark:hover:text-amber-300 transition">
                Apply Now
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Short Courses Spotlight -->
    <section v-if="shortCourses && shortCourses.length" class="py-20 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
          <div>
            <p class="text-amber-600 dark:text-amber-400 text-sm font-bold uppercase tracking-wider mb-2">Workshops & Short Courses</p>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">Learn a New Skill Today</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Flexible short courses and cooking sessions for all skill levels.</p>
          </div>
          <Link :href="route('programmes')" class="group inline-flex items-center gap-2 text-amber-600 dark:text-amber-400 font-semibold hover:text-amber-700 dark:hover:text-amber-300 transition">
            View all courses
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </Link>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="course in shortCourses" :key="course.id" class="group relative bg-gradient-to-br from-gray-50 to-amber-50/30 dark:from-gray-800 dark:to-amber-900/20 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-3">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-semibold rounded-full">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Short Course
              </span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-300 transition">{{ course.name }}</h3>
            <p v-if="course.description" class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-4">{{ course.description }}</p>
            <div v-if="course.start_date" class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <span>{{ new Date(course.start_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-white dark:bg-gray-900 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <span class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium rounded-full mb-4">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            Student Stories
          </span>
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">What Our Alumni Say</h2>
          <p class="mt-3 text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Hear from our graduates about their journey at HBCI and their success in the industry.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-gradient-to-br from-gray-50 to-amber-50/30 dark:from-gray-800 dark:to-amber-900/10 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300 group">
            <div class="flex gap-1 mb-4">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <p class="text-gray-700 dark:text-gray-300 italic mb-6">"The hands-on training at HBCI gave me the confidence to work in top kitchens. Within months of graduating, I secured a position at a renowned restaurant in Cape Town."</p>
            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold text-sm">LM</div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">Lerato Mokoena</p>
                <p class="text-sm text-amber-600 dark:text-amber-400">Professional Chef, Class of 2023</p>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-gray-50 to-amber-50/30 dark:from-gray-800 dark:to-amber-900/10 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300 group">
            <div class="flex gap-1 mb-4">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <p class="text-gray-700 dark:text-gray-300 italic mb-6">"The instructors' industry experience is unmatched. They don't just teach theory—they share real-world insights that helped me excel in my hotel management career."</p>
            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold text-sm">TK</div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">Thabo Kolobe</p>
                <p class="text-sm text-amber-600 dark:text-amber-400">Hotel Manager, Class of 2022</p>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-gray-50 to-amber-50/30 dark:from-gray-800 dark:to-amber-900/10 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-600 transition-all duration-300 group">
            <div class="flex gap-1 mb-4">
              <svg v-for="i in 5" :key="i" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <p class="text-gray-700 dark:text-gray-300 italic mb-6">"Thanks to HBCI's international certifications, I had job offers from luxury hotels even before my graduation ceremony. The investment was worth it."</p>
            <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold text-sm">MR</div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">Mamokhele Rali</p>
                <p class="text-sm text-amber-600 dark:text-amber-400">Pastry Chef, Class of 2024</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-amber-600 to-amber-700 relative overflow-hidden">
      <!-- Background decoration -->
      <div class="absolute top-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-white/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-3xl"></div>

      <!-- Decorative elements -->
      <div class="absolute top-16 left-16 w-20 h-20 border-2 border-white/10 rounded-full"></div>
      <div class="absolute bottom-16 right-16 w-12 h-12 border border-white/10 rounded-full"></div>

      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white text-sm font-medium mb-6">
          <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
          Now Enrolling for 2026
        </div>
        <h2 class="text-3xl lg:text-4xl font-bold text-white">Ready to Start Your Culinary Journey?</h2>
        <p class="mt-4 text-amber-100 text-lg max-w-2xl mx-auto">Applications are now open for the upcoming academic year. Take the first step toward your dream career today.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
          <Link :href="route('apply')" class="group inline-flex items-center gap-2 px-8 py-4 bg-white text-amber-700 font-bold rounded-lg hover:bg-amber-50 transition shadow-lg hover:shadow-xl hover:-translate-y-0.5">
            Apply Now — It's Free
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </Link>
          <Link :href="route('contact')" class="group inline-flex items-center gap-2 px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition">
            Have Questions?
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.79 4 4 0 .89-.449 1.68-1.13 2.137A4.826 4.826 0 0112 14c-2.21 0-4-1.79-4-4 0-1.17.56-2.21 1.41-2.86A4.694 4.694 0 004.5 4C3.12 4 2 5.12 2 6.5c0 1.64 1.24 2.98 2.81 3.14"/></svg>
          </Link>
        </div>
      </div>
    </section>

    <!-- Scroll to Top Button -->
    <Transition name="fade">
      <button
        v-if="showScrollTop"
        @click="scrollToTop"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-amber-600 text-white rounded-full shadow-lg hover:bg-amber-700 hover:shadow-xl transition-all flex items-center justify-center"
        aria-label="Scroll to top"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
      </button>
    </Transition>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  programmes: Array,
  shortCourses: Array,
  announcements: Array,
});

const showScrollTop = ref(false);

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 600;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-12px); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
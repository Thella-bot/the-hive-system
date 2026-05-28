<template>
  <button
    type="button"
    @click="toggleTheme"
    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
    :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
  >
    <SunIcon v-if="isDark" class="h-5 w-5" />
    <MoonIcon v-else class="h-5 w-5" />
  </button>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline';

const isDark = ref(false);

const initTheme = () => {
  const saved = localStorage.getItem('theme');
  if (saved) {
    isDark.value = saved === 'dark';
  } else {
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  applyTheme();
};

const applyTheme = () => {
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
};

const toggleTheme = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  applyTheme();
};

onMounted(initTheme);
</script>

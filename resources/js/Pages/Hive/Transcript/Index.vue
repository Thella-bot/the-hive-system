<template>
  <HiveLayout title="Transcript" description="View your academic transcript">
    <div class="max-w-5xl mx-auto">
      <div class="mb-5">
        <Link :href="route('hive.dashboard')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm flex items-center gap-1">
          <ArrowLeftIcon class="w-4 h-4" />
          Back to Dashboard
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-5 border-b border-slate-200 bg-slate-50/80 px-6 py-6 sm:flex-row sm:items-end sm:justify-between dark:border-slate-700 dark:bg-slate-800/60">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">Academic record</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">My Transcript</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ student.name }} <span class="mx-1 text-slate-300 dark:text-slate-600">/</span> {{ student.student_number || student.profile?.student_number || 'Student record' }}</p>
          </div>
          <a :href="route('hive.transcript.download', { student: student.id })"
             class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-slate-700 dark:bg-amber-500 dark:text-slate-950 dark:hover:bg-amber-400">
            <ArrowDownTrayIcon class="w-4 h-4" />
            Download PDF
          </a>
        </div>

        <div v-if="modulesByYear && Object.keys(modulesByYear).length" class="space-y-8 p-6">
          <div v-for="(yearModules, year) in modulesByYear" :key="year" class="space-y-3">
            <div class="flex items-center justify-between gap-4">
              <h2 class="text-sm font-black uppercase tracking-[0.14em] text-slate-700 dark:text-slate-200">Academic Year {{ year }}</h2>
              <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ yearModules.length }} modules</span>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full min-w-[620px] text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/70">
                  <tr class="text-left text-[11px] font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                    <th class="rounded-l-lg px-4 py-3">Code</th>
                    <th class="px-4 py-3">Module</th>
                    <th class="px-4 py-3 text-center">Credits</th>
                    <th class="px-4 py-3 text-center">Assessments</th>
                    <th class="rounded-r-lg px-4 py-3 text-right">Grade</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="module in yearModules" :key="module.id" class="text-slate-700 dark:text-slate-200">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ module.code }}</td>
                    <td class="px-4 py-3 font-semibold">{{ module.name }}</td>
                    <td class="px-4 py-3 text-center">{{ module.credits }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 dark:text-slate-400">{{ module.gradedCount }}/{{ module.totalGradables }}</td>
                    <td class="px-4 py-3 text-right font-black">
                      <span :class="module.averageGrade !== null ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                      {{ module.averageGrade !== null ? module.averageGrade + '%' : 'N/A' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <p v-else class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No modules enrolled.</p>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
  student: Object,
  modulesByYear: Object,
});
</script>

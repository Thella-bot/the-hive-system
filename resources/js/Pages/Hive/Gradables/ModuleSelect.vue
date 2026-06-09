<script setup>
import { Link } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const { isStudent, isAcademicStaff } = useUser();

const props = defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: 'quiz',
    },
    typeLabel: {
        type: String,
        default: 'Quizzes',
    },
});

const typeIcons = {
    quiz: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    test: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l-2 2 2 2m4-4l2 2-2 2',
    assignment: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    mid_term_exam: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    final_exam: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
};

const typeColors = {
    quiz: 'bg-blue-100 dark:bg-blue-900/30',
    test: 'bg-green-100 dark:bg-green-900/30',
    assignment: 'bg-yellow-100 dark:bg-yellow-900/30',
    mid_term_exam: 'bg-purple-100 dark:bg-purple-900/30',
    final_exam: 'bg-red-100 dark:bg-red-900/30',
};
</script>

<template>
    <HiveLayout
        :title="typeLabel"
        :description="`Select a module to view ${typeLabel.toLowerCase()}`"
    >
        <div class="mb-6">
            <p class="text-gray-600 dark:text-gray-400">
                {{ isStudent ? 'Select a module to view and submit your assessments.' : 'Select a module to manage assessments.' }}
            </p>
        </div>

        <!-- Module Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
                v-for="mod in modules"
                :key="mod.id"
                :href="route('hive.gradables.index', { module_id: mod.id, type: type })"
                class="block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-amber-500 dark:hover:border-amber-500 transition-all"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="p-3 rounded-lg" :class="typeColors[type] || typeColors.quiz">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="typeIcons[type] || typeIcons.quiz" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        {{ mod.code }}
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ mod.name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                    {{ mod.description || 'No description available' }}
                </p>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ mod.credits || 0 }} Credits
                    </span>
                    <span class="text-amber-600 dark:text-amber-400 font-medium">View {{ typeLabel }} →</span>
                </div>
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="modules.length === 0" class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No modules found</h3>
            <p class="text-gray-500 dark:text-gray-400">
                {{ isStudent ? 'You are not enrolled in any modules with this assessment type.' : 'No modules available to display.' }}
            </p>
        </div>
    </HiveLayout>
</template>
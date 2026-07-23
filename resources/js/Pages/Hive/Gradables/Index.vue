<template>
    <HiveLayout :title="gradables.length + ' Assessments'" :description="isStudent ? 'View and submit your assessments' : 'Manage all assessments'">
        <div class="space-y-6">
            <!-- Create Button -->
            <div class="flex justify-end">
                <Link v-if="canCreate" :href="route('hive.gradables.create')"
                    class="inline-flex items-center px-4 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Create Assessment
                </Link>
            </div>

            <!-- Type Filter -->
            <div class="flex gap-2 flex-wrap">
                <Link :href="route('hive.gradables.index')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="!$page.url.includes('type=') ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
                    All
                </Link>
                <Link v-for="type in assessmentTypes" :key="type.value"
                    :href="route('hive.gradables.index', { type: type.value })"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url.includes(`type=${type.value}`) ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
                    {{ type.label }}
                </Link>
            </div>

            <!-- Assessments Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Module</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ canCreate ? 'Actions' : 'View' }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="gradable in gradables" :key="gradable.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ gradable.title }}</div>
                                    <div v-if="gradable.max_marks" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Max: {{ gradable.max_marks }} | Weight: {{ gradable.weight || 0 }}%
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="getGradableTypeClass(gradable.type)">
                                        {{ formatType(gradable.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ gradable.module?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(gradable.due_date) }}</div>
                                    <div class="text-xs" :class="isOverdue(gradable.due_date) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                        {{ isOverdue(gradable.due_date) ? 'Overdue' : getTimeRemaining(gradable.due_date) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="isOverdue(gradable.due_date)" class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Closed</span>
                                    <span v-else class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Open</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('hive.gradables.show', { gradable: gradable.id })"
                                        class="text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300 font-medium">
                                        {{ canCreate ? 'Manage' : 'View' }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="gradables.length === 0" class="text-center py-16">
                    <DocumentIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No assessments found</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ canCreate ? 'Create your first assessment to get started.' : 'No assessments available for your modules.' }}
                    </p>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { PlusIcon, DocumentIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { useUser } from '@/composables/useUser';

dayjs.extend(relativeTime);

defineProps({
    gradables: {
        type: Array,
        default: () => [],
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const { isStudent } = useUser();

const assessmentTypes = [
    { value: 'quiz', label: 'Quizzes' },
    { value: 'test', label: 'Tests' },
    { value: 'assignment', label: 'Assignments' },
    { value: 'mid_term_exam', label: 'Mid-Term Exams' },
    { value: 'final_exam', label: 'Final Exams' },
];

const formatDate = (date) => {
    return dayjs(date).format('MMM D, YYYY h:mm A');
};

const isOverdue = (date) => {
    return dayjs(date).isBefore(dayjs());
};

const getTimeRemaining = (date) => {
    return dayjs(date).fromNow();
};

const formatType = (type) => {
    const labels = {
        quiz: 'Quiz',
        test: 'Test',
        assignment: 'Assignment',
        mid_term_exam: 'Mid-Term',
        final_exam: 'Final Exam',
    };
    return labels[type] || type;
};

const getGradableTypeClass = (type) => {
    const typeClasses = {
        quiz: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        test: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        assignment: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        mid_term_exam: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        final_exam: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return typeClasses[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};
</script>
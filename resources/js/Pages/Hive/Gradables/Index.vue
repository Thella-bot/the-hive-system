<template>
    <HiveLayout title="Assessments" :description="isStudent ? 'View and submit your assessments' : 'Manage all assessments'">
        <!-- Header with Create Button (Instructors/Admins only) -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Assessments</h1>
            <Link
                v-if="canCreate"
                :href="route('hive.gradables.create')"
                class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition ease-in-out duration-150"
            >
                Create Assessment
            </Link>
        </div>

        <!-- Type Filter -->
        <div class="mb-4 flex gap-2 flex-wrap">
            <Link
                :href="route('hive.gradables.index')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="!$page.url.includes('type=') ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            >
                All
            </Link>
            <Link
                v-for="type in assessmentTypes"
                :key="type.value"
                :href="route('hive.gradables.index', { type: type.value })"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="$page.url.includes(`type=${type.value}`) ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            >
                {{ type.label }}
            </Link>
        </div>

        <!-- Gradables Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th v-if="canCreate" scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                        <th v-else scope="col" class="relative px-6 py-3">
                            <span class="sr-only">View</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="gradable in gradables" :key="gradable.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ gradable.title }}</div>
                            <div v-if="gradable.description" class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                {{ gradable.description }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getGradableTypeClass(gradable.type)">
                                {{ formatType(gradable.type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ gradable.module?.name || 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ formatDate(gradable.due_date) }}</div>
                            <div class="text-xs" :class="isOverdue(gradable.due_date) ? 'text-red-600' : 'text-green-600'">
                                {{ isOverdue(gradable.due_date) ? 'Overdue' : getTimeRemaining(gradable.due_date) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="isOverdue(gradable.due_date)" class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Closed</span>
                            <span v-else class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Open</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link
                                :href="route('hive.gradables.show', gradable.id)"
                                class="text-amber-600 hover:text-amber-900 font-medium"
                            >
                                {{ canCreate ? 'Manage' : 'View' }}
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="gradables.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No assessments found</h3>
                <p class="text-gray-500">
                    {{ canCreate ? 'Create your first assessment to get started.' : 'No assessments available for your modules.' }}
                </p>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    gradables: {
        type: Array,
        default: () => [],
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const isStudent = computed(() => page.props.auth.user?.roles?.includes('student'));

const assessmentTypes = [
    { value: 'quiz', label: 'Quizzes' },
    { value: 'test', label: 'Tests' },
    { value: 'assignment', label: 'Assignments' },
    { value: 'mid-term_exam', label: 'Mid-Term Exams' },
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
        quiz: 'bg-blue-100 text-blue-800',
        test: 'bg-green-100 text-green-800',
        assignment: 'bg-yellow-100 text-yellow-800',
        mid_term_exam: 'bg-purple-100 text-purple-800',
        final_exam: 'bg-red-100 text-red-800',
    };
    return typeClasses[type] || 'bg-gray-100 text-gray-800';
};
</script>
<template>
    <HiveLayout
        :title="gradable.title"
        :description="`${formatType(gradable.type)} - ${gradable.module?.name || 'Unknown Module'}`"
    >
        <div class="max-w-4xl mx-auto">
            <!-- Gradable Details Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ gradable.title }}</h1>
                                <span class="px-3 py-1 text-sm rounded-full" :class="getGradableTypeClass(gradable.type)">
                                    {{ formatType(gradable.type) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ isOverdue(gradable.due_date) ? 'Closed' : 'Open' }} until {{ formatDate(gradable.due_date) }}
                            </p>
                        </div>

                        <!-- Actions for Instructors/Admins -->
                        <div v-if="isInstructor" class="flex gap-2">
                            <Link
                                :href="route('hive.gradables.edit', gradable.id)"
                                class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteGradable"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <!-- Description -->
                    <div v-if="gradable.description" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                        <p class="text-gray-600">{{ gradable.description }}</p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Due Date</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ formatDate(gradable.due_date) }}</p>
                        </div>
                        <div v-if="gradable.duration_minutes" class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Duration</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ gradable.duration_minutes }} min</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Max Marks</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ gradable.max_marks || 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Weight (%)</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ gradable.weight || 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Module</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ gradable.module?.name || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Instructor Info -->
                    <div v-if="gradable.instructor" class="flex items-center gap-3 text-sm text-gray-600">
                        <img
                            v-if="gradable.instructor.profile_photo_url"
                            :src="gradable.instructor.profile_photo_url"
                            class="w-8 h-8 rounded-full"
                        />
                        <div>
                            <span class="text-gray-500">Created by:</span>
                            <span class="font-medium ml-1">{{ gradable.instructor.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student's Submission Section -->
            <div v-if="!isInstructor && studentSubmission" class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Your Submission</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Submitted: {{ formatDate(studentSubmission.created_at) }}</p>
                            <p v-if="studentSubmission.remarks" class="text-sm text-gray-600 mt-1">{{ studentSubmission.remarks }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button for Students -->
            <div v-if="!isInstructor && !studentSubmission && !isOverdue(gradable.due_date)" class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Submit Your Work</h3>
                </div>
                <div class="px-6 py-4">
                    <form @submit.prevent="submitWork">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks (Optional)</label>
                            <textarea
                                v-model="form.remarks"
                                rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="Add any notes about your submission..."
                            ></textarea>
                        </div>
                        <PrimaryButton type="submit" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                        </PrimaryButton>
                    </form>
                </div>
            </div>

            <!-- Closed Notice for Students -->
            <div v-if="!isInstructor && isOverdue(gradable.due_date)" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-1">Submission Closed</h3>
                <p class="text-sm text-red-600">The deadline for this assessment has passed.</p>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import dayjs from 'dayjs';

const props = defineProps({
    gradable: {
        type: Object,
        required: true,
    },
    isInstructor: {
        type: Boolean,
        default: false,
    },
    studentSubmission: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const form = ref({
    remarks: '',
});
const isSubmitting = ref(false);

const formatDate = (date) => {
    return dayjs(date).format('MMM D, YYYY h:mm A');
};

const isOverdue = (date) => {
    return dayjs(date).isBefore(dayjs());
};

const formatType = (type) => {
    const labels = {
        quiz: 'Quiz',
        test: 'Test',
        assignment: 'Assignment',
        mid_term_exam: 'Mid-Term Exam',
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

const submitWork = async () => {
    isSubmitting.value = true;
    try {
        await router.post(route('submissions.store', { gradable: props.gradable.id }), {
            remarks: form.value.remarks,
        });
    } finally {
        isSubmitting.value = false;
    }
};

const deleteGradable = () => {
    if (confirm('Are you sure you want to delete this assessment?')) {
        router.delete(route('hive.gradables.destroy', props.gradable.id));
    }
};
</script>
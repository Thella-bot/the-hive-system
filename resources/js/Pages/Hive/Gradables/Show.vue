<template>
    <HiveLayout
        :title="gradable.title"
        :description="`${formatType(gradable.type)} - ${gradable.module?.name || 'Unknown Module'}`"
    >
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Gradable Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ gradable.title }}</h1>
                            <span class="px-3 py-1 text-sm rounded-full" :class="getGradableTypeClass(gradable.type)">
                                {{ formatType(gradable.type) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
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

                <div class="px-6 py-4">
                    <!-- Description -->
                    <div v-if="gradable.description" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description / Instructions</h3>
                        <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ gradable.description }}</p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(gradable.due_date) }}</p>
                        </div>
                        <div v-if="gradable.duration_minutes" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Duration</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.duration_minutes }} min</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Max Marks</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.max_marks || 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Weight (%)</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.weight || 'N/A' }}</p>
                        </div>
                        <div v-if="gradable.max_file_size" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Max File Size</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ (gradable.max_file_size / 1024).toFixed(1) }} MB</p>
                        </div>
                        <div v-if="gradable.allowed_types" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Allowed Types</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.allowed_types }}</p>
                        </div>
                    </div>

                    <!-- Instructor Info -->
                    <div v-if="gradable.instructor" class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="w-8 h-8 bg-amber-200 dark:bg-amber-700 rounded-full flex items-center justify-center text-amber-800 dark:text-amber-200 font-semibold text-sm">
                            {{ gradable.instructor.name.charAt(0) }}
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Created by:</span>
                            <span class="font-medium ml-1">{{ gradable.instructor.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attachments</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ gradable.attachments?.length || 0 }} files</span>
                </div>

                <div class="px-6 py-4">
                    <!-- Existing Attachments -->
                    <div v-if="gradable.attachments && gradable.attachments.length" class="space-y-3 mb-4">
                        <div v-for="attachment in gradable.attachments" :key="attachment.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ attachment.title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ attachment.human_file_size }}</p>
                                </div>
                            </div>
                            <a :href="route('hive.gradables.attachments.download', { gradable: gradable.id, attachment: attachment.id })"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No attachments</p>

                    <!-- Upload New Attachment (Instructors Only) -->
                    <div v-if="isInstructor" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="uploadAttachment">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Add New Attachment</h4>
                            <div class="flex gap-3">
                                <input type="text" v-model="attachmentForm.title"
                                    placeholder="File title"
                                    class="flex-1 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm">
                                <input type="file" @input="attachmentForm.file = $event.target.files[0]"
                                    class="flex-1 text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100">
                                <button type="submit" :disabled="attachmentForm.processing"
                                    class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 disabled:opacity-50 transition">
                                    {{ attachmentForm.processing ? 'Uploading...' : 'Upload' }}
                                </button>
                            </div>
                            <p v-if="attachmentForm.error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ attachmentForm.error }}</p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Student's Submission Section -->
            <div v-if="!isInstructor && studentSubmission" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Submission</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Submitted: {{ formatDate(studentSubmission.created_at) }}</p>
                            <p v-if="studentSubmission.remarks" class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ studentSubmission.remarks }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button for Students -->
            <div v-if="!isInstructor && !studentSubmission && !isOverdue(gradable.due_date)" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Submit Your Work</h3>
                </div>
                <div class="px-6 py-4">
                    <form @submit.prevent="submitWork">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">File *</label>
                            <input type="file" @input="form.file = $event.target.files[0]" required
                                class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Max size: {{ gradable.max_file_size ? (gradable.max_file_size / 1024).toFixed(0) + ' MB' : '20 MB' }}
                                <span v-if="gradable.allowed_types"> | Types: {{ gradable.allowed_types }}</span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Remarks (Optional)</label>
                            <textarea
                                v-model="form.remarks"
                                rows="2"
                                class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"
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
            <div v-if="!isInstructor && isOverdue(gradable.due_date)" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-red-800 dark:text-red-200 mb-1">Submission Closed</h3>
                <p class="text-sm text-red-600 dark:text-red-400">The deadline for this assessment has passed.</p>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
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
    file: null,
    remarks: '',
});
const isSubmitting = ref(false);

const attachmentForm = reactive({
    title: '',
    file: null,
    processing: false,
    error: '',
});

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
        quiz: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        test: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        assignment: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        mid_term_exam: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        final_exam: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return typeClasses[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const submitWork = async () => {
    if (!form.file) return;
    isSubmitting.value = true;
    try {
        const data = new FormData();
        data.append('file', form.file);
        if (form.remarks) data.append('remarks', form.remarks);
        await router.post(route('hive.submissions.store', { gradable: props.gradable.id }), data, {
            forceFormData: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};

const deleteGradable = () => {
    if (confirm('Are you sure you want to delete this assessment? This action cannot be undone.')) {
        router.delete(route('hive.gradables.destroy', props.gradable.id));
    }
};

const uploadAttachment = async () => {
    if (!attachmentForm.file) {
        attachmentForm.error = 'Please select a file';
        return;
    }

    attachmentForm.processing = true;
    attachmentForm.error = '';

    const data = new FormData();
    data.append('title', attachmentForm.title || attachmentForm.file.name);
    data.append('file', attachmentForm.file);

    try {
        await router.post(route('hive.gradables.attachments.store', { gradable: props.gradable.id }), data, {
            forceFormData: true,
        });
        attachmentForm.title = '';
        attachmentForm.file = null;
    } catch (e) {
        attachmentForm.error = 'Failed to upload attachment';
    } finally {
        attachmentForm.processing = false;
    }
};
</script>
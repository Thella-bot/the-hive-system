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
                            <span v-if="gradable.submission_type" class="px-2 py-0.5 text-xs rounded" :class="getSubmissionTypeClass(gradable.submission_type)">
                                {{ formatSubmissionType(gradable.submission_type) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isOverdue(gradable.due_date) ? 'Closed' : 'Open' }} until {{ formatDate(gradable.due_date) }}
                        </p>
                    </div>

                    <div v-if="isInstructor" class="flex gap-2">
                        <Link :href="route('hive.gradables.edit', { gradable: gradable.id })"
                            class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 transition">
                            Edit / Questions
                        </Link>
                        <button @click="deleteGradable"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div v-if="gradable.description" class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description</h3>
                        <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ gradable.description }}</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Due Date</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(gradable.due_date) }}</p>
                        </div>
                        <div v-if="gradable.duration_minutes" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Duration</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.duration_minutes }} min</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Max Marks</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.max_marks || 'N/A' }}</p>
                        </div>
                        <div v-if="gradable.time_limit_minutes" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Time Limit</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ gradable.time_limit_minutes }} min</p>
                        </div>
                    </div>

                    <div v-if="gradable.instructor" class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 bg-amber-200 dark:bg-amber-700 rounded-full flex items-center justify-center text-amber-800 dark:text-amber-200 font-semibold text-sm">
                            {{ gradable.instructor.name.charAt(0) }}
                        </div>
                        <span class="text-gray-600 dark:text-gray-300">By {{ gradable.instructor.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attachments</h3>
                </div>
                <div class="px-6 py-4">
                    <div v-if="gradable.attachments && gradable.attachments.length" class="space-y-2">
                        <div v-for="att in gradable.attachments" :key="att.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm text-gray-900 dark:text-white">{{ att.title }}</span>
                            <a :href="route('hive.gradables.attachments.download', { gradable: gradable.id, attachment: att.id })"
                                class="text-amber-600 hover:text-amber-700 text-sm font-medium">Download</a>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No attachments</p>
                </div>
            </div>

            <!-- Online Questions for Students -->
            <div v-if="isStudent && gradable.submission_type && gradable.submission_type !== 'file_upload'"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Questions</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ gradable.questions?.length || 0 }} questions</p>
                </div>

                <form @submit.prevent="submitOnlineAnswers" class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="(q, qIdx) in gradable.questions" :key="q.id" class="p-6">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="flex-shrink-0 w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center text-amber-700 dark:text-amber-300 font-semibold text-sm">
                                {{ qIdx + 1 }}
                            </span>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 text-xs rounded-full" :class="getQuestionTypeClass(q.type)">
                                        {{ formatQuestionType(q.type) }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ q.points }} pts</span>
                                </div>
                                <p class="text-gray-900 dark:text-white font-medium mb-3">{{ q.question_text }}</p>

                                <!-- MCQ -->
                                <div v-if="q.type === 'multiple_choice'" class="space-y-2 ml-4">
                                    <label v-for="opt in q.options" :key="opt.id"
                                        class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition"
                                        :class="selectedAnswers[q.id]?.option_id === opt.id
                                            ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                                            : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                        <input type="radio" :name="'q_' + q.id" :value="opt.id"
                                            v-model="selectedAnswers[q.id].option_id"
                                            class="text-amber-600 focus:ring-amber-500" />
                                        <span class="text-gray-700 dark:text-gray-200">{{ opt.option_text }}</span>
                                    </label>
                                </div>

                                <!-- Fill in blank / short answer -->
                                <div v-else-if="q.type === 'fill_in_blank' || q.type === 'short_answer'" class="ml-4">
                                    <input type="text" v-model="selectedAnswers[q.id].answer_text"
                                        class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm"
                                        :placeholder="q.type === 'fill_in_blank' ? 'Type your answer...' : 'Enter your answer...'">
                                </div>

                                <!-- Essay -->
                                <div v-else-if="q.type === 'essay'" class="ml-4">
                                    <textarea v-model="selectedAnswers[q.id].answer_text" rows="4"
                                        class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm"
                                        placeholder="Write your answer..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                        <div v-if="$page.props.flash?.success" class="mb-3 p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg text-sm">
                            {{ $page.props.flash.success }}
                        </div>
                        <button type="submit" :disabled="submittingAnswers || isOverdue(gradable.due_date)"
                            class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 disabled:opacity-50 transition">
                            {{ submittingAnswers ? 'Submitting...' : 'Submit Answers' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- File Upload Submission for Students -->
            <div v-if="isStudent && gradable.submission_type === 'file_upload' && !studentSubmission && !isOverdue(gradable.due_date)"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Submit Your Work</h3>
                </div>
                <div class="px-6 py-4">
                    <form @submit.prevent="submitWork" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">File *</label>
                            <input type="file" @input="form.file = $event.target.files[0]" required
                                class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300">
                        </div>
                        <button type="submit" :disabled="isSubmitting"
                            class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 disabled:opacity-50 transition">
                            {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Already Submitted -->
            <div v-if="!isInstructor && studentSubmission" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                <CheckCircleIcon class="w-12 h-12 mx-auto text-green-500 mb-3" />
                <h3 class="text-lg font-medium text-green-800 dark:text-green-200">Already Submitted</h3>
                <p class="text-sm text-green-600 dark:text-green-400">Submitted on {{ formatDate(studentSubmission.submitted_at || studentSubmission.created_at) }}</p>
            </div>

            <!-- Closed -->
            <div v-if="!isInstructor && isOverdue(gradable.due_date)" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
                <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Submission Closed</h3>
                <p class="text-sm text-red-600 dark:text-red-400">The deadline has passed.</p>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    gradable: Object,
    isInstructor: Boolean,
    isStudent: Boolean,
    studentSubmission: Object,
    studentAnswers: Object,
});

const form = ref({ file: null });
const isSubmitting = ref(false);
const submittingAnswers = ref(false);

const selectedAnswers = reactive({});
if (props.studentAnswers) {
    Object.entries(props.studentAnswers).forEach(([qId, ans]) => {
        selectedAnswers[qId] = { option_id: ans.gradable_question_option_id, answer_text: ans.answer_text };
    });
}
if (props.gradable.questions) {
    props.gradable.questions.forEach(q => {
        if (!selectedAnswers[q.id]) selectedAnswers[q.id] = { option_id: null, answer_text: '' };
    });
}

const formatDate = (d) => dayjs(d).format('MMM D, YYYY h:mm A');
const isOverdue = (d) => dayjs(d).isBefore(dayjs());
const formatType = (t) => ({ quiz: 'Quiz', test: 'Test', assignment: 'Assignment', mid_term_exam: 'Mid-Term', final_exam: 'Final' }[t] || t);
const formatSubmissionType = (t) => ({ file_upload: 'File Upload', online_fillable: 'Online Fill', online_multiple_choice: 'Online MCQ' }[t] || t);
const formatQuestionType = (t) => ({ multiple_choice: 'MCQ', fill_in_blank: 'Fill Blank', short_answer: 'Short Ans', essay: 'Essay' }[t] || t);
const getGradableTypeClass = (t) => ({ quiz: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', test: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', assignment: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', mid_term_exam: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300', final_exam: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }[t] || 'bg-gray-100');
const getSubmissionTypeClass = (t) => ({ file_upload: 'bg-gray-100 text-gray-800', online_fillable: 'bg-green-100 text-green-800', online_multiple_choice: 'bg-blue-100 text-blue-800' }[t] || 'bg-gray-100');
const getQuestionTypeClass = (t) => ({ multiple_choice: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', fill_in_blank: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', short_answer: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', essay: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' }[t] || 'bg-gray-100');

const submitWork = async () => {
    if (!form.value.file) return;
    isSubmitting.value = true;
    const data = new FormData();
    data.append('file', form.value.file);
    await router.post(route('hive.submissions.store', { gradable: props.gradable.id }), data, { forceFormData: true });
    isSubmitting.value = false;
};

const submitOnlineAnswers = async () => {
    submittingAnswers.value = true;
    const answers = Object.entries(selectedAnswers).map(([qId, data]) => ({
        question_id: parseInt(qId),
        option_id: data.option_id ? parseInt(data.option_id) : null,
        answer_text: data.answer_text || null,
    }));
    await router.post(route('hive.gradables.submit-online', { gradable: props.gradable.id }), { answers });
    submittingAnswers.value = false;
};

const deleteGradable = () => {
    if (confirm('Delete this assessment?')) router.delete(route('hive.gradables.destroy', { gradable: props.gradable.id }));
};
</script>
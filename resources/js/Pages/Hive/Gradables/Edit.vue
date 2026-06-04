<template>
    <HiveLayout title="Edit Assessment">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Basic Info Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Assessment</h1>
                    <Link :href="route('hive.gradables.show', { gradable: gradable.id })"
                        class="text-sm text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300">
                        Back to Assessment
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Title *</label>
                        <input type="text" v-model="form.title" id="title" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Type *</label>
                            <select v-model="form.type" id="type" required
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option v-for="type in gradableTypes" :key="type" :value="type">{{ formatType(type) }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="submission_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Submission Type *</label>
                            <select v-model="form.submission_type" id="submission_type" required
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option v-for="st in submissionTypes" :key="st.value" :value="st.value">{{ st.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="module" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Module *</label>
                            <select v-model="form.module_id" id="module" required
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option v-for="module in modules" :key="module.id" :value="module.id">{{ module.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Due Date *</label>
                        <input type="datetime-local" v-model="form.due_date" id="due_date" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Duration (minutes)</label>
                            <input type="number" v-model="form.duration_minutes" id="duration_minutes" min="0"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label for="max_marks" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max Marks</label>
                            <input type="number" v-model="form.max_marks" id="max_marks" min="0"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Weight (%)</label>
                            <input type="number" v-model="form.weight" id="weight" min="0" max="100" step="0.01"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <!-- Online Assessment Options -->
                    <div v-if="form.submission_type && form.submission_type !== 'file_upload'" class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Online Assessment Options</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Time Limit (minutes)</label>
                                <input type="number" v-model="form.time_limit_minutes" id="time_limit_minutes" min="1"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                            <div>
                                <label for="max_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max Attempts</label>
                                <input type="number" v-model="form.max_attempts" id="max_attempts" min="1"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="form.shuffle_questions"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Shuffle questions</span>
                            </label>
                            <label v-if="form.submission_type === 'online_multiple_choice'" class="inline-flex items-center">
                                <input type="checkbox" v-model="form.shuffle_options"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Shuffle options</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="form.show_correct_answers"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Show correct answers after submission</span>
                            </label>
                        </div>
                    </div>

                    <!-- File Upload Options -->
                    <div v-if="form.submission_type === 'file_upload'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max File Size (KB)</label>
                            <input type="number" v-model="form.max_file_size" id="max_file_size" min="1" max="102400"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label for="allowed_types" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Allowed File Types</label>
                            <input type="text" v-model="form.allowed_types" id="allowed_types" placeholder="pdf,doc,docx"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description / Instructions</label>
                        <textarea v-model="form.description" id="description" rows="3"
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link :href="route('hive.gradables.show', { gradable: gradable.id })"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 transition">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Questions Section (for online assessments) -->
            <div v-if="gradable.submission_type && gradable.submission_type !== 'file_upload'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Questions</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ gradable.questions?.length || 0 }} questions</p>
                    </div>
                </div>

                <!-- Existing Questions -->
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="(question, qIndex) in gradable.questions" :key="question.id" class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Q{{ qIndex + 1 }}</span>
                                    <span class="px-2 py-0.5 text-xs rounded-full" :class="getQuestionTypeClass(question.type)">
                                        {{ formatQuestionType(question.type) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ question.points }} pts</span>
                                    <span v-if="question.is_required" class="text-xs text-red-500">*Required</span>
                                </div>
                                <p class="text-gray-900 dark:text-white font-medium">{{ question.question_text }}</p>
                                <p v-if="question.explanation" class="text-sm text-gray-500 dark:text-gray-400 mt-1">Explanation: {{ question.explanation }}</p>
                            </div>
                            <button @click="deleteQuestion(question.id)" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                        </div>
                        <!-- MCQ Options -->
                        <div v-if="question.type === 'multiple_choice' && question.options" class="ml-4 mt-3 space-y-2">
                            <div v-for="(option, oIndex) in question.options" :key="option.id"
                                class="flex items-center gap-2 p-2 rounded-lg" :class="option.is_correct ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700/50'">
                                <input type="radio" :checked="option.is_correct" disabled class="text-amber-600" />
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ option.option_text }}</span>
                                <span v-if="option.is_correct" class="text-xs text-green-600 dark:text-green-400">Correct</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Question Form -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Add New Question</h4>
                    <form @submit.prevent="addQuestion" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="q_type" class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Question Type *</label>
                                <select v-model="newQuestion.type" id="q_type" required
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="fill_in_blank">Fill in the Blank</option>
                                    <option value="short_answer">Short Answer</option>
                                    <option value="essay">Essay</option>
                                </select>
                            </div>
                            <div>
                                <label for="q_points" class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Points</label>
                                <input type="number" v-model="newQuestion.points" id="q_points" min="1" value="1"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="q_text" class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Question *</label>
                            <textarea v-model="newQuestion.question_text" id="q_text" rows="2" required
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm"
                                placeholder="Enter your question..."></textarea>
                        </div>

                        <!-- MCQ Options -->
                        <div v-if="newQuestion.type === 'multiple_choice'" class="space-y-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Options (mark correct answers)</label>
                            <div v-for="(opt, idx) in newQuestion.options" :key="idx" class="flex items-center gap-2">
                                <input type="checkbox" v-model="opt.is_correct" class="rounded border-gray-300 dark:border-gray-600 text-amber-600" />
                                <input type="text" v-model="opt.option_text" required
                                    class="flex-1 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm"
                                    :placeholder="'Option ' + (idx + 1)">
                                <button type="button" @click="removeOption(idx)" v-if="newQuestion.options.length > 2"
                                    class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                            </div>
                            <button type="button" @click="addOption"
                                class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400">+ Add Option</button>
                        </div>

                        <div>
                            <label for="q_explanation" class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Explanation (shown after answering)</label>
                            <input type="text" v-model="newQuestion.explanation" id="q_explanation"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm"
                                placeholder="Optional explanation...">
                        </div>

                        <button type="submit" :disabled="addingQuestion"
                            class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 disabled:opacity-50 transition">
                            {{ addingQuestion ? 'Adding...' : 'Add Question' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ $page.props.flash.success }}</p>
            </div>
            <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $page.props.flash.error }}</p>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    gradable: Object,
    modules: Array,
    gradableTypes: Array,
    submissionTypes: Array,
});

const form = useForm({
    title: props.gradable.title,
    type: props.gradable.type instanceof Object ? props.gradable.type.value : props.gradable.type,
    submission_type: props.gradable.submission_type || 'file_upload',
    module_id: props.gradable.module_id,
    due_date: props.gradable.due_date ? props.gradable.due_date.slice(0, 16) : '',
    duration_minutes: props.gradable.duration_minutes || '',
    time_limit_minutes: props.gradable.time_limit_minutes || '',
    max_attempts: props.gradable.max_attempts || '',
    show_correct_answers: props.gradable.show_correct_answers || false,
    shuffle_questions: props.gradable.shuffle_questions || false,
    shuffle_options: props.gradable.shuffle_options || false,
    description: props.gradable.description || '',
    max_marks: props.gradable.max_marks || '',
    weight: props.gradable.weight || '',
    max_file_size: props.gradable.max_file_size || '',
    allowed_types: props.gradable.allowed_types || '',
});

const newQuestion = reactive({
    type: 'multiple_choice',
    question_text: '',
    points: 1,
    explanation: '',
    options: [
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
    ],
});

const addingQuestion = ref(false);

const submit = () => {
    form.put(route('hive.gradables.update', { gradable: props.gradable.id }));
};

const addOption = () => {
    newQuestion.options.push({ option_text: '', is_correct: false });
};

const removeOption = (index) => {
    newQuestion.options.splice(index, 1);
};

const addQuestion = () => {
    addingQuestion.value = true;
    const data = new FormData();
    data.append('type', newQuestion.type);
    data.append('question_text', newQuestion.question_text);
    data.append('points', newQuestion.points);
    data.append('explanation', newQuestion.explanation || '');

    if (newQuestion.type === 'multiple_choice') {
        newQuestion.options.forEach((opt, idx) => {
            data.append(`options[${idx}][option_text]`, opt.option_text);
            data.append(`options[${idx}][is_correct]`, opt.is_correct ? '1' : '0');
        });
    }

    router.post(route('hive.gradables.questions.store', { gradable: props.gradable.id }), data, {
        forceFormData: true,
        onFinish: () => {
            addingQuestion.value = false;
            newQuestion.question_text = '';
            newQuestion.explanation = '';
            newQuestion.options = [
                { option_text: '', is_correct: false },
                { option_text: '', is_correct: false },
                { option_text: '', is_correct: false },
                { option_text: '', is_correct: false },
            ];
        },
    });
};

const deleteQuestion = (questionId) => {
    if (confirm('Delete this question?')) {
        router.delete(route('hive.gradables.questions.destroy', { gradable: props.gradable.id, question: questionId }));
    }
};

const formatType = (type) => type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

const formatQuestionType = (type) => {
    const labels = {
        multiple_choice: 'MCQ',
        fill_in_blank: 'Fill in Blank',
        short_answer: 'Short Answer',
        essay: 'Essay',
    };
    return labels[type] || type;
};

const getQuestionTypeClass = (type) => {
    const classes = {
        multiple_choice: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        fill_in_blank: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        short_answer: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        essay: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    };
    return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};
</script>
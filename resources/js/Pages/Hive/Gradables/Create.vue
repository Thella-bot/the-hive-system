<template>
    <HiveLayout title="Create Assessment">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Create Assessment</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Title *</label>
                        <input type="text" v-model="form.title" id="title" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Type *</label>
                        <select v-model="form.type" id="type" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            <option value="" disabled>Select type</option>
                            <option v-for="type in gradableTypes" :key="type" :value="type">{{ formatType(type) }}</option>
                        </select>
                    </div>

                    <!-- Module -->
                    <div>
                        <label for="module" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Module *</label>
                        <select v-model="form.module_id" id="module" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            <option value="" disabled>Select module</option>
                            <option v-for="module in modules" :key="module.id" :value="module.id">{{ module.name }}</option>
                        </select>
                    </div>

                    <!-- Submission Type -->
                    <div>
                        <label for="submission_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Submission Type *</label>
                        <select v-model="form.submission_type" id="submission_type" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            <option value="" disabled>Select submission type</option>
                            <option v-for="st in submissionTypes" :key="st.value" :value="st.value">{{ st.label }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="form.submission_type === 'file_upload'">Students will upload files as their submission.</span>
                            <span v-else-if="form.submission_type === 'online_fillable'">Students answer fill-in-the-blank and short answer questions online.</span>
                            <span v-else-if="form.submission_type === 'online_multiple_choice'">Students answer multiple choice questions online.</span>
                        </p>
                    </div>

                    <!-- Online Assessment Options (shown when submission_type is online) -->
                    <div v-if="form.submission_type && form.submission_type !== 'file_upload'" class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Online Assessment Options</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Time Limit (minutes)</label>
                                <input type="number" v-model="form.time_limit_minutes" id="time_limit_minutes" min="1" placeholder="No limit"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                            <div>
                                <label for="max_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max Attempts</label>
                                <input type="number" v-model="form.max_attempts" id="max_attempts" min="1" placeholder="Unlimited"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="form.shuffle_questions"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Shuffle questions for each student</span>
                            </label>
                            <label v-if="form.submission_type === 'online_multiple_choice'" class="inline-flex items-center">
                                <input type="checkbox" v-model="form.shuffle_options"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Shuffle options for each question</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="form.show_correct_answers"
                                    class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Show correct answers after submission</span>
                            </label>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Due Date *</label>
                        <input type="datetime-local" v-model="form.due_date" id="due_date" required
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <!-- Two Column Row: Duration + Max Marks -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Duration (minutes)</label>
                            <input type="number" v-model="form.duration_minutes" id="duration_minutes" min="0" placeholder="e.g. 60"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label for="max_marks" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max Marks</label>
                            <input type="number" v-model="form.max_marks" id="max_marks" min="0" placeholder="e.g. 100"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <!-- Weight -->
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Weight (% of total grade)</label>
                        <input type="number" v-model="form.weight" id="weight" min="0" max="100" step="0.01" placeholder="e.g. 25"
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <!-- File Upload Options (shown for file_upload type) -->
                    <div v-if="form.submission_type === 'file_upload'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max File Size (KB)</label>
                                <input type="number" v-model="form.max_file_size" id="max_file_size" min="1" max="102400" placeholder="e.g. 20480"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                            <div>
                                <label for="allowed_types" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Allowed File Types</label>
                                <input type="text" v-model="form.allowed_types" id="allowed_types" placeholder="pdf,doc,docx"
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description / Instructions</label>
                        <textarea v-model="form.description" id="description" rows="4" placeholder="Add any instructions or details..."
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="form.hasErrors" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p v-for="error in form.errors" :key="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link :href="route('hive.gradables.index')"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-6 py-2.5 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition">
                            {{ form.processing ? 'Creating...' : 'Create Assessment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    modules: Array,
    gradableTypes: Array,
    submissionTypes: Array,
});

const form = useForm({
    title: '',
    type: '',
    submission_type: '',
    module_id: '',
    due_date: '',
    duration_minutes: '',
    time_limit_minutes: '',
    max_attempts: '',
    show_correct_answers: false,
    shuffle_questions: false,
    shuffle_options: false,
    description: '',
    max_marks: '',
    weight: '',
    max_file_size: '',
    allowed_types: '',
});

const formatType = (type) => {
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const submit = () => {
    form.post(route('hive.gradables.store'));
};
</script>
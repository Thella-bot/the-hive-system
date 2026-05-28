<template>
    <HiveLayout title="Edit Assessment">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Assessment</h1>
                    <Link :href="route('hive.gradables.show', gradable.id)"
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Type *</label>
                            <select v-model="form.type" id="type" required
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option v-for="type in gradableTypes" :key="type" :value="type">{{ formatType(type) }}</option>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Weight (%)</label>
                            <input type="number" v-model="form.weight" id="weight" min="0" max="100" step="0.01"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                        <div>
                            <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max File Size (KB)</label>
                            <input type="number" v-model="form.max_file_size" id="max_file_size" min="1" max="102400"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="allowed_types" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Allowed File Types</label>
                        <input type="text" v-model="form.allowed_types" id="allowed_types" placeholder="pdf,doc,docx"
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comma-separated extensions</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description / Instructions</label>
                        <textarea v-model="form.description" id="description" rows="4"
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="form.hasErrors" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p v-for="error in form.errors" :key="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link :href="route('hive.gradables.show', gradable.id)"
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
        </div>
    </HiveLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    gradable: Object,
    modules: Array,
    gradableTypes: Array,
});

const form = useForm({
    title: props.gradable.title,
    type: props.gradable.type instanceof Object ? props.gradable.type.value : props.gradable.type,
    module_id: props.gradable.module_id,
    due_date: props.gradable.due_date ? props.gradable.due_date.slice(0, 16) : '',
    duration_minutes: props.gradable.duration_minutes || '',
    description: props.gradable.description || '',
    max_marks: props.gradable.max_marks || '',
    weight: props.gradable.weight || '',
    max_file_size: props.gradable.max_file_size || '',
    allowed_types: props.gradable.allowed_types || '',
});

const submit = () => {
    form.put(route('hive.gradables.update', props.gradable.id));
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
</script>
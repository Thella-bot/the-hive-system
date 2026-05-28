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
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Percentage this assessment contributes to final grade</p>
                    </div>

                    <!-- Two Column Row: Max File Size + Allowed Types -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Max File Size (KB)</label>
                            <input type="number" v-model="form.max_file_size" id="max_file_size" min="1" max="102400" placeholder="e.g. 20480"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank for default</p>
                        </div>
                        <div>
                            <label for="allowed_types" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Allowed File Types</label>
                            <input type="text" v-model="form.allowed_types" id="allowed_types" placeholder="pdf,doc,docx"
                                class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comma-separated, e.g. pdf,doc,docx</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Description / Instructions</label>
                        <textarea v-model="form.description" id="description" rows="4" placeholder="Add any instructions or details..."
                            class="block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Attachments</label>
                        <div class="space-y-3">
                            <div v-for="(attachment, index) in attachments" :key="index" class="flex items-center gap-3">
                                <input type="text" v-model="attachment.title" :placeholder="'File ' + (index + 1) + ' title'"
                                    class="flex-1 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white text-sm">
                                <input type="file" @change="handleFileChange($event, index)"
                                    class="flex-1 text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/50">
                                <button type="button" @click="removeAttachment(index)"
                                    class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addAttachment"
                            class="mt-3 inline-flex items-center px-3 py-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Attachment
                        </button>
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
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    modules: Array,
    gradableTypes: Array,
});

const attachments = ref([]);

const form = useForm({
    title: '',
    type: '',
    module_id: '',
    due_date: '',
    duration_minutes: '',
    description: '',
    max_marks: '',
    weight: '',
    max_file_size: '',
    allowed_types: '',
});

const formatType = (type) => {
    return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const addAttachment = () => {
    attachments.value.push({ title: '', file: null });
};

const removeAttachment = (index) => {
    attachments.value.splice(index, 1);
};

const handleFileChange = (event, index) => {
    attachments.value[index].file = event.target.files[0];
};

const submit = () => {
    const data = new FormData();
    data.append('title', form.title);
    data.append('type', form.type);
    data.append('module_id', form.module_id);
    data.append('due_date', form.due_date);

    if (form.duration_minutes) data.append('duration_minutes', form.duration_minutes);
    if (form.description) data.append('description', form.description);
    if (form.max_marks) data.append('max_marks', form.max_marks);
    if (form.weight) data.append('weight', form.weight);
    if (form.max_file_size) data.append('max_file_size', form.max_file_size);
    if (form.allowed_types) data.append('allowed_types', form.allowed_types);

    attachments.value.forEach((attachment, index) => {
        if (attachment.file) {
            data.append(`attachments[${index}][title]`, attachment.title || attachment.file.name);
            data.append(`attachments[${index}][file]`, attachment.file);
        }
    });

    form.post(route('hive.gradables.store'), {
        data,
        forceFormData: true,
    });
};
</script>
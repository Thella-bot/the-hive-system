<template>
    <HiveLayout title="Edit Assessment">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Edit Assessment</h1>
                <Link
                    :href="route('hive.gradables.show', gradable.id)"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Back to Assessment
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow-lg rounded-lg p-8">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input
                        type="text"
                        v-model="form.title"
                        id="title"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select
                            v-model="form.type"
                            id="type"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        >
                            <option v-for="type in gradableTypes" :key="type" :value="type">{{ formatType(type) }}</option>
                        </select>
                        <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                    </div>

                    <div>
                        <label for="module" class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                        <select
                            v-model="form.module_id"
                            id="module"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        >
                            <option v-for="module in modules" :key="module.id" :value="module.id">
                                {{ module.name }}
                            </option>
                        </select>
                        <p v-if="errors.module_id" class="mt-1 text-sm text-red-600">{{ errors.module_id }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input
                        type="datetime-local"
                        v-model="form.due_date"
                        id="due_date"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                    />
                    <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="max_marks" class="block text-sm font-medium text-gray-700 mb-2">Max Marks</label>
                        <input
                            type="number"
                            v-model="form.max_marks"
                            id="max_marks"
                            min="0"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        />
                    </div>

                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (%)</label>
                        <input
                            type="number"
                            v-model="form.weight"
                            id="weight"
                            min="0"
                            max="100"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        />
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        v-model="form.description"
                        id="description"
                        rows="4"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-4">
                    <Link
                        :href="route('hive.gradables.show', gradable.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-25 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
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
    errors: Object,
});

const form = useForm({
    title: props.gradable.title,
    type: props.gradable.type instanceof Object ? props.gradable.type.value : props.gradable.type,
    module_id: props.gradable.module_id,
    due_date: props.gradable.due_date ? props.gradable.due_date.slice(0, 16) : '',
    description: props.gradable.description || '',
    max_marks: props.gradable.max_marks || '',
    weight: props.gradable.weight || '',
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
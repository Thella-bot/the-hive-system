<template>
    <HiveLayout title="Create Gradable">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Create Gradable</h1>
            <form @submit.prevent="submit" class="bg-white shadow-lg rounded-lg p-8">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" v-model="form.title" id="title" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select v-model="form.type" id="type" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option v-for="type in gradableTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label for="module" class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                    <select v-model="form.module_id" id="module" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option v-for="module in modules" :key="module.id" :value="module.id">{{ module.name }}</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input type="datetime-local" v-model="form.due_date" id="due_date" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-6">
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                    <input type="number" v-model="form.duration_minutes" id="duration_minutes" min="0" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea v-model="form.description" id="description" rows="4" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    modules: Array,
    gradableTypes: Array,
});

const form = useForm({
    title: '',
    type: '',
    module_id: '',
    due_date: '',
    duration_minutes: '',
    description: '',
});

const submit = () => {
    form.post(route('hive.gradables.store'));
};
</script>
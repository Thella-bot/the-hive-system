<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    module: Object,
    categories: Object,
});

const form = useForm({
    title: '',
    description: '',
    file: null,
    category: 'general',
    is_published: true,
});

const submit = () => {
    form.post(route('hive.modules.courses.store', props.module));
};
</script>

<template>
    <Head title="Upload Course Material" />
    <HiveLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Upload Course Material</h2>
            <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input
                                type="text"
                                v-model="form.title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            />
                            <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select
                                v-model="form.category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option v-for="(label, key) in categories" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                            <p v-if="form.errors.category" class="text-red-500 text-sm mt-1">{{ form.errors.category }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">File</label>
                            <input
                                type="file"
                                @change="form.file = $event.target.files[0]"
                                class="mt-1 block w-full"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">Max file size: 50MB</p>
                            <p v-if="form.errors.file" class="text-red-500 text-sm mt-1">{{ form.errors.file }}</p>
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.is_published"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                            />
                            <label class="ml-2 text-sm text-gray-700">Published (visible to students)</label>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                            >
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    module: Object,
    lessonPlan: Object,
});
</script>

<template>
    <Head title="Lesson Plan" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ lessonPlan.title }}</h2>
                    <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
                </div>
                <a
                    :href="route('hive.modules.lesson-plans.edit', [module, lessonPlan])"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Edit
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <span class="text-sm text-gray-500">
                                {{ new Date(lessonPlan.lesson_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                            </span>
                            <span v-if="lessonPlan.start_time" class="text-sm text-gray-500 ml-2">
                                {{ lessonPlan.start_time }} - {{ lessonPlan.end_time }}
                            </span>
                        </div>

                        <div v-if="lessonPlan.description" class="mb-6">
                            <h3 class="font-semibold text-lg mb-2">Description</h3>
                            <p class="text-gray-700">{{ lessonPlan.description }}</p>
                        </div>

                        <div v-if="lessonPlan.objectives" class="mb-6">
                            <h3 class="font-semibold text-lg mb-2">Objectives</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ lessonPlan.objectives }}</p>
                        </div>

                        <div v-if="lessonPlan.content" class="mb-6">
                            <h3 class="font-semibold text-lg mb-2">Content</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ lessonPlan.content }}</p>
                        </div>

                        <div v-if="lessonPlan.resources" class="mb-6">
                            <h3 class="font-semibold text-lg mb-2">Resources</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ lessonPlan.resources }}</p>
                        </div>

                        <div v-if="lessonPlan.assessment" class="mb-6">
                            <h3 class="font-semibold text-lg mb-2">Assessment</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ lessonPlan.assessment }}</p>
                        </div>

                        <div class="mt-6 pt-6 border-t text-sm text-gray-500">
                            <p>Created by {{ lessonPlan.creator?.name }}</p>
                            <p>Last updated {{ new Date(lessonPlan.updated_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

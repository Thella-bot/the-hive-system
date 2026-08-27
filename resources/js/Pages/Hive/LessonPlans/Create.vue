<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    module: Object,
    timetableSlots: Array,
});

const form = useForm({
    title: '',
    description: '',
    objectives: '',
    content: '',
    resources: '',
    assessment: '',
    lesson_date: '',
    start_time: '',
    end_time: '',
    timetable_slot_id: '',
    status: 'draft',
});

const submit = () => {
    form.post(route('hive.modules.lesson-plans.store', props.module));
};
</script>

<template>
    <Head title="Create Lesson Plan" />
    <HiveLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Lesson Plan</h2>
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
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Objectives</label>
                            <textarea
                                v-model="form.objectives"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="What students will learn..."
                            ></textarea>
                            <p v-if="form.errors.objectives" class="text-red-500 text-sm mt-1">{{ form.errors.objectives }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content</label>
                            <textarea
                                v-model="form.content"
                                rows="5"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="Lesson content and activities..."
                            ></textarea>
                            <p v-if="form.errors.content" class="text-red-500 text-sm mt-1">{{ form.errors.content }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Resources</label>
                            <textarea
                                v-model="form.resources"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="Materials needed..."
                            ></textarea>
                            <p v-if="form.errors.resources" class="text-red-500 text-sm mt-1">{{ form.errors.resources }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assessment</label>
                            <textarea
                                v-model="form.assessment"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="How learning will be assessed..."
                            ></textarea>
                            <p v-if="form.errors.assessment" class="text-red-500 text-sm mt-1">{{ form.errors.assessment }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lesson Date</label>
                            <input
                                type="date"
                                v-model="form.lesson_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            />
                            <p v-if="form.errors.lesson_date" class="text-red-500 text-sm mt-1">{{ form.errors.lesson_date }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input
                                    type="time"
                                    v-model="form.start_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                                <p v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <input
                                    type="time"
                                    v-model="form.end_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                                <p v-if="form.errors.end_time" class="text-red-500 text-sm mt-1">{{ form.errors.end_time }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Link to Timetable Slot</label>
                            <select
                                v-model="form.timetable_slot_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">None</option>
                                <option v-for="slot in timetableSlots" :key="slot.id" :value="slot.id">
                                    {{ slot.day_of_week }} {{ slot.start_time }} - {{ slot.end_time }}
                                </option>
                            </select>
                            <p v-if="form.errors.timetable_slot_id" class="text-red-500 text-sm mt-1">{{ form.errors.timetable_slot_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                            >
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

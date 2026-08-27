<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    modules: Array,
    rooms: Array,
    days: Array,
});

const form = useForm({
    module_id: '',
    instructor_id: '',
    room_id: '',
    day_of_week: '',
    start_time: '',
    end_time: '',
    semester: '',
    academic_year: '',
    start_date: '',
    end_date: '',
    recurrence: 'weekly',
});

const submit = () => {
    form.post(route('hive.timetable.store'));
};
</script>

<template>
    <Head title="Add Timetable Slot" />
    <HiveLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Timetable Slot</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Module</label>
                            <select
                                v-model="form.module_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">Select Module</option>
                                <option v-for="mod in modules" :key="mod.id" :value="mod.id">
                                    {{ mod.code }} - {{ mod.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.module_id" class="text-red-500 text-sm mt-1">{{ form.errors.module_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Instructor</label>
                            <input
                                type="number"
                                v-model="form.instructor_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="Instructor ID"
                                required
                            />
                            <p v-if="form.errors.instructor_id" class="text-red-500 text-sm mt-1">{{ form.errors.instructor_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Room</label>
                            <select
                                v-model="form.room_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">Select Room (Optional)</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    {{ room.name }} ({{ room.building || 'N/A' }}) - Capacity: {{ room.capacity }}
                                </option>
                            </select>
                            <p v-if="form.errors.room_id" class="text-red-500 text-sm mt-1">{{ form.errors.room_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Day of Week</label>
                            <select
                                v-model="form.day_of_week"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">Select Day</option>
                                <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
                            </select>
                            <p v-if="form.errors.day_of_week" class="text-red-500 text-sm mt-1">{{ form.errors.day_of_week }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input
                                    type="time"
                                    v-model="form.start_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <p v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <input
                                    type="time"
                                    v-model="form.end_time"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <p v-if="form.errors.end_time" class="text-red-500 text-sm mt-1">{{ form.errors.end_time }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Semester</label>
                                <input
                                    type="text"
                                    v-model="form.semester"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="1"
                                    required
                                />
                                <p v-if="form.errors.semester" class="text-red-500 text-sm mt-1">{{ form.errors.semester }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <input
                                    type="text"
                                    v-model="form.academic_year"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="2026/2027"
                                    required
                                />
                                <p v-if="form.errors.academic_year" class="text-red-500 text-sm mt-1">{{ form.errors.academic_year }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input
                                    type="date"
                                    v-model="form.start_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <p v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input
                                    type="date"
                                    v-model="form.end_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <p v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recurrence</label>
                            <select
                                v-model="form.recurrence"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Biweekly</option>
                                <option value="once">Once</option>
                            </select>
                            <p v-if="form.errors.recurrence" class="text-red-500 text-sm mt-1">{{ form.errors.recurrence }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                            >
                                Create Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

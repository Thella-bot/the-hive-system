<template>
    <Head title="Edit Placement" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-2xl font-bold mb-6">Edit Placement</h1>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Student</label>
                            <select v-model="form.student_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Organisation Name</label>
                            <input v-model="form.organisation_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Organisation Address</label>
                            <textarea v-model="form.organisation_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Supervisor Name</label>
                            <input v-model="form.supervisor_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Supervisor Contact</label>
                            <input v-model="form.supervisor_contact" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input v-model="form.start_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input v-model="form.end_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Duration</label>
                            <input v-model="form.duration" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="Compulsory">Compulsory</option>
                                <option value="Elective">Elective</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Learning Objectives</label>
                            <textarea v-model="form.learning_objectives" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <Link :href="route('hive.placements.index')" class="btn btn-secondary">
                                Cancel
                            </Link>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    placement: Object,
    students: Array,
});

const form = useForm({
    student_id: props.placement.student_id,
    programme_id: props.placement.programme_id,
    organisation_name: props.placement.organisation_name,
    organisation_address: props.placement.organisation_address,
    supervisor_name: props.placement.supervisor_name,
    supervisor_contact: props.placement.supervisor_contact,
    start_date: props.placement.start_date,
    end_date: props.placement.end_date,
    duration: props.placement.duration,
    type: props.placement.type,
    status: props.placement.status,
    learning_objectives: props.placement.learning_objectives,
});

function submit() {
    form.put(route('hive.placements.update', props.placement.id));
}
</script>

<template>
    <Head title="Edit Disciplinary Action" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-2xl font-bold mb-6">Edit Disciplinary Action</h1>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">User</label>
                            <select v-model="form.user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="warning">Warning</option>
                                <option value="suspension">Suspension</option>
                                <option value="expulsion">Expulsion</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Offence</label>
                            <input v-model="form.offence" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Incident Description</label>
                            <textarea v-model="form.incident_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    action: Object,
    users: Array,
});

const form = useForm({
    user_id: props.action.user_id,
    type: props.action.type,
    offence: props.action.offence,
    incident_description: props.action.incident_description,
});

function submit() {
    form.put(route('hive.disciplinary.update', props.action.id));
}
</script>

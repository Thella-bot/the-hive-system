<template>
    <Head :title="'Placement: ' + placement.organisation_name" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">{{ placement.organisation_name }}</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('hive.placements.edit', placement.id)" class="btn btn-primary">
                            Edit
                        </Link>
                        <Link :href="route('hive.placements.index')" class="btn btn-secondary">
                            Back
                        </Link>
                    </div>
                </div>

                <!-- Placement Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>Student:</strong> {{ placement.student.name }}</div>
                    <div><strong>Programme:</strong> {{ placement.programme.name }}</div>
                    <div><strong>Organisation:</strong> {{ placement.organisation_name }}</div>
                    <div><strong>Address:</strong> {{ placement.organisation_address }}</div>
                    <div><strong>Supervisor:</strong> {{ placement.supervisor_name }}</div>
                    <div><strong>Supervisor Contact:</strong> {{ placement.supervisor_contact }}</div>
                    <div><strong>Start Date:</strong> {{ placement.start_date }}</div>
                    <div><strong>End Date:</strong> {{ placement.end_date }}</div>
                    <div><strong>Duration:</strong> {{ placement.duration }}</div>
                    <div><strong>Type:</strong> {{ placement.type }}</div>
                    <div><strong>Status:</strong> {{ placement.status || 'Pending' }}</div>
                </div>

                <!-- PDF Actions -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-3">Documents</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a :href="route('hive.placements.letter-pdf', placement.id)" target="_blank" class="btn btn-success text-center">
                            <i class="fas fa-file-pdf"></i> Placement Request Letter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    placement: {
        type: Object,
        required: true,
    },
});
</script>
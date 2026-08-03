<template>
    <Head :title="'Disciplinary: ' + action.offence" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">{{ action.offence }}</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('disciplinary.edit', action.id)" class="btn btn-primary">
                            Edit
                        </Link>
                        <Link :href="route('disciplinary.index')" class="btn btn-secondary">
                            Back
                        </Link>
                    </div>
                </div>

                <!-- Action Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>User:</strong> {{ action.user.name }}</div>
                    <div><strong>Type:</strong> <span class="font-bold">{{ action.type.toUpperCase() }}</span></div>
                    <div><strong>Warning Level:</strong> {{ action.warning_level || 'N/A' }}</div>
                    <div><strong>Hearing Date:</strong> {{ action.hearing_date }}</div>
                    <div><strong>Effective Date:</strong> {{ action.effective_date }}</div>
                    <div><strong>Duration:</strong> {{ action.duration || 'N/A' }}</div>
                    <div class="col-span-2"><strong>Incident Description:</strong> {{ action.incident_description }}</div>
                    <div class="col-span-2" v-if="action.grounds"><strong>Grounds:</strong> {{ action.grounds.join(', ') }}</div>
                    <div class="col-span-2" v-if="action.corrective_actions"><strong>Corrective Actions:</strong> {{ action.corrective_actions.join(', ') }}</div>
                </div>

                <!-- PDF Actions -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-3">Documents</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a :href="route('disciplinary.warning-pdf', action.id)" target="_blank" class="btn btn-warning text-center">
                            <i class="fas fa-file-pdf"></i> Warning Letter
                        </a>
                        <a :href="route('disciplinary.suspension-pdf', action.id)" target="_blank" class="btn btn-danger text-center">
                            <i class="fas fa-file-pdf"></i> Suspension Letter
                        </a>
                        <a :href="route('disciplinary.expulsion-pdf', action.id)" target="_blank" class="btn btn-danger text-center">
                            <i class="fas fa-file-pdf"></i> Expulsion Letter
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
    action: {
        type: Object,
        required: true,
    },
});
</script>
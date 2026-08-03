<template>
    <Head title="Disciplinary Actions" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Disciplinary Actions</h1>
                    <Link :href="route('disciplinary.create')" class="btn btn-primary">
                        + New Action
                    </Link>
                </div>
                <div class="p-6 border-t">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2 text-left">User</th>
                                <th class="border p-2 text-left">Type</th>
                                <th class="border p-2 text-left">Offence</th>
                                <th class="border p-2 text-left">Hearing Date</th>
                                <th class="border p-2 text-left">Status</th>
                                <th class="border p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="action in actions.data" :key="action.id">
                                <td class="border p-2">{{ action.user.name }}</td>
                                <td class="border p-2">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800 px-2 py-1 rounded': action.type === 'warning',
                                        'bg-orange-100 text-orange-800 px-2 py-1 rounded': action.type === 'suspension',
                                        'bg-red-100 text-red-800 px-2 py-1 rounded': action.type === 'expulsion',
                                    }">
                                        {{ action.type.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="border p-2">{{ action.offence }}</td>
                                <td class="border p-2">{{ action.hearing_date }}</td>
                                <td class="border p-2">{{ action.status || 'Active' }}</td>
                                <td class="border p-2">
                                    <Link :href="route('disciplinary.show', action.id)" class="text-blue-600 hover:underline">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">
                    <Pagination :links="actions.links" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    actions: {
        type: Object,
        required: true,
    },
});
</script>
<template>
    <Head :title="'Staff: ' + staff.name" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">{{ staff.name }}</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('hive.staff.edit', staff.id)" class="btn btn-primary">
                            Edit Staff
                        </Link>
                    </div>
                </div>

                <!-- Staff Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>Employee Number:</strong> {{ staff.employee_number || 'N/A' }}</div>
                    <div><strong>Email:</strong> {{ staff.email }}</div>
                    <div><strong>Position:</strong> {{ staff.position || 'N/A' }}</div>
                    <div><strong>Department:</strong> {{ staff.department || 'N/A' }}</div>
                    <div><strong>Role:</strong> {{ staff.roles.map(r => r.name).join(', ') }}</div>
                    <div><strong>Phone:</strong> {{ staff.phone || 'N/A' }}</div>
                </div>

                <!-- Payroll -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-3">Payroll</h2>
                    <div class="flex gap-4">
                        <Link :href="route('hive.payslips.index')" class="text-amber-600 hover:text-amber-700 font-medium">
                            View Payslips
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    staff: {
        type: Object,
        required: true,
    },
});
</script>
<template>
    <Head :title="'Invoice: ' + invoice.invoice_number" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Invoice {{ invoice.invoice_number }}</h1>
                    <Link :href="route('hive.finance.invoices.index')" class="btn btn-secondary">
                        Back to Invoices
                    </Link>
                </div>

                <!-- Invoice Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>Client:</strong> {{ invoice.user?.name || '—' }}</div>
                    <div><strong>Email:</strong> {{ invoice.user?.email || '—' }}</div>
                    <div><strong>Type:</strong> {{ invoice.type }}</div>
                    <div><strong>Academic Year:</strong> {{ invoice.academic_year || '—' }}</div>
                    <div><strong>Amount:</strong> <span class="font-bold">M {{ invoice.amount }}</span></div>
                    <div><strong>Due Date:</strong> {{ invoice.due_date || 'Not set' }}</div>
                    <div class="col-span-2">
                        <div class="flex items-center gap-4">
                            <strong>Status:</strong>
                            <span :class="{
                                'bg-yellow-100 text-yellow-800 px-2 py-1 rounded': invoice.status === 'pending',
                                'bg-green-100 text-green-800 px-2 py-1 rounded': invoice.status === 'paid',
                                'bg-red-100 text-red-800 px-2 py-1 rounded': invoice.status === 'overdue',
                                'bg-gray-100 text-gray-800 px-2 py-1 rounded': invoice.status === 'cancelled',
                                'bg-blue-100 text-blue-800 px-2 py-1 rounded': invoice.status === 'partial',
                            }">
                                {{ invoice.status.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <strong>Total Paid:</strong> <span class="text-green-600">M {{ invoice.total_paid }}</span>
                    </div>
                    <div class="col-span-2">
                        <strong>Balance Due:</strong> <span class="font-bold" :class="{ 'text-red-600': invoice.balance > 0 }">M {{ invoice.balance }}</span>
                    </div>
                </div>

                <!-- Payments -->
                <div v-if="invoice.payments?.length" class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Payment History</h2>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2 text-left">Date</th>
                                <th class="border p-2 text-right">Amount</th>
                                <th class="border p-2 text-left">Method</th>
                                <th class="border p-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in invoice.payments" :key="payment.id">
                                <td class="border p-2">{{ payment.payment_date }}</td>
                                <td class="border p-2 text-right">M {{ payment.amount }}</td>
                                <td class="border p-2">{{ payment.payment_method }}</td>
                                <td class="border p-2 capitalize">{{ payment.status }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});
</script>


<template>
    <Head :title="'Payment: ' + payment.payment_reference" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Payment {{ payment.payment_reference }}</h1>
                    <div class="flex gap-2">
                        <a :href="route('hive.finance.payments.receipt', payment.id)" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                            Download Receipt
                        </a>
                        <Link :href="route('hive.finance.payments.index')" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Back to Payments
                        </Link>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>Receipt No:</strong> {{ payment.payment_reference }}</div>
                    <div><strong>Payer:</strong> {{ payment.user?.name || '—' }}</div>
                    <div><strong>Amount:</strong> <span class="font-bold">M {{ payment.amount }}</span></div>
                    <div><strong>Method:</strong> {{ payment.payment_method }}</div>
                    <div><strong>Date:</strong> {{ payment.payment_date }}</div>
                    <div><strong>Status:</strong> <span class="capitalize">{{ payment.status }}</span></div>
                    <div v-if="payment.notes"><strong>Notes:</strong> {{ payment.notes }}</div>
                    <div v-if="payment.invoice"><strong>Invoice:</strong> {{ payment.invoice.invoice_number }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    payment: {
        type: Object,
        required: true,
    },
});
</script>


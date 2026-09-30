<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    requests: Object,
    filters: Object,
    pendingCount: Number,
});

const decide = useForm({
    status: '',
    review_note: '',
});

const reviewing = ref(null);

const openReview = (request) => {
    reviewing.value = request;
    decide.status = '';
    decide.review_note = '';
    decide.errors = {};
};

const closeReview = () => {
    reviewing.value = null;
    decide.reset();
};

const submit = () => {
    decide.patch(route('hive.enrollment.requests.decide', reviewing.value.id), {
        preserveScroll: true,
        onSuccess: () => closeReview(),
    });
};

const typeLabel = (type) => (type === 'deregistration' ? 'Drop' : 'Enroll');

const statusClasses = (status) => ({
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head title="Enrollment Requests" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Enrollment Requests
                </h2>
                <a
                    :href="route('hive.enrollment.admin.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Back to Enrollments
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    v-if="pendingCount > 0"
                    class="mb-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-900"
                >
                    {{ pendingCount }} request{{ pendingCount === 1 ? '' : 's' }} awaiting a decision.
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                :value="filters?.status ?? 'pending'"
                                @change="$inertia.get(route('hive.enrollment.requests'), { status: $event.target.value, type: filters?.type ?? '' })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="">All</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select
                                :value="filters?.type ?? ''"
                                @change="$inertia.get(route('hive.enrollment.requests'), { status: filters?.status ?? 'pending', type: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All</option>
                                <option value="enrollment">Enrollment</option>
                                <option value="deregistration">Deregistration</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Requests table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="request in requests.data" :key="request.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ request.user?.name }}</div>
                                    <div class="text-sm text-gray-500">{{ request.user?.student_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ typeLabel(request.type) }} &mdash; {{ request.module?.code }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ request.module?.name }}</div>
                                    <div v-if="request.reason" class="text-xs text-gray-500 mt-1 italic">
                                        &ldquo;{{ request.reason }}&rdquo;
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ request.academic_year }} &middot; Sem {{ request.semester }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 rounded-md text-xs font-medium capitalize"
                                        :class="statusClasses(request.status)"
                                    >
                                        {{ request.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        v-if="request.status === 'pending'"
                                        @click="openReview(request)"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        Review
                                    </button>
                                    <span v-else class="text-sm text-gray-400">&mdash;</span>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No enrollment requests found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="requests.last_page > 1" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-1">
                            <Link
                                v-for="page in requests.links"
                                :key="page.label"
                                :href="page.url ?? '#'"
                                class="px-3 py-1 text-sm rounded-md border"
                                :class="page.active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700'"
                                v-html="page.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review modal -->
        <div v-if="reviewing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ typeLabel(reviewing.type) }} request
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ reviewing.user?.name }} &mdash; {{ reviewing.module?.code }} {{ reviewing.module?.name }}
                </p>

                <label class="block text-sm font-medium text-gray-700 mb-1" for="review_note">
                    Note (optional)
                </label>
                <textarea
                    id="review_note"
                    v-model="decide.review_note"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm"
                />
                <p v-if="decide.errors.review_note" class="mt-1 text-sm text-red-600">
                    {{ decide.errors.review_note }}
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        @click="closeReview"
                        class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="decide.status = 'rejected'; submit()"
                        :disabled="decide.processing"
                        class="px-4 py-2 rounded-md bg-red-600 text-sm text-white hover:bg-red-700 disabled:opacity-50"
                    >
                        Reject
                    </button>
                    <button
                        @click="decide.status = 'approved'; submit()"
                        :disabled="decide.processing"
                        class="px-4 py-2 rounded-md bg-green-600 text-sm text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        Approve
                    </button>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

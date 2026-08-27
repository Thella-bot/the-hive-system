<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    student: Object,
    assessment: Object,
    history: Array,
});

const form = useForm({
    notes: '',
});

const submit = () => {
    form.post(route('hive.advancement.promote', props.student.id));
};
</script>

<template>
    <Head title="Student Details" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ student.name }}</h2>
                    <p class="text-sm text-gray-600">{{ student.profile?.student_number }}</p>
                </div>
                <a :href="route('hive.advancement.index')" class="text-indigo-600 hover:text-indigo-900">
                    Back to List
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Assessment Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4">Current Assessment</h3>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">Year Level</p>
                                <p class="text-xl font-bold">Year {{ assessment.year_level }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">Enrolled</p>
                                <p class="text-xl font-bold">{{ assessment.modules_enrolled }}/{{ assessment.total_required_modules }}</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded">
                                <p class="text-sm text-gray-600">Passed</p>
                                <p class="text-xl font-bold text-green-600">{{ assessment.modules_passed }}</p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded">
                                <p class="text-sm text-gray-600">Failed</p>
                                <p class="text-xl font-bold text-red-600">{{ assessment.modules_failed }}</p>
                            </div>
                        </div>

                        <div
                            v-if="assessment.can_graduate"
                            class="p-4 bg-purple-100 text-purple-800 rounded mb-4"
                        >
                            <strong>Status:</strong> Eligible for Graduation
                        </div>
                        <div
                            v-else-if="assessment.eligible"
                            class="p-4 bg-green-100 text-green-800 rounded mb-4"
                        >
                            <strong>Status:</strong> Eligible for Promotion
                        </div>
                        <div
                            v-else
                            class="p-4 bg-yellow-100 text-yellow-800 rounded mb-4"
                        >
                            <strong>Status:</strong> Not Eligible - {{ assessment.reason }}
                        </div>

                        <div v-if="assessment.eligible || assessment.can_graduate">
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        placeholder="Add any notes about this promotion..."
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                                >
                                    {{ assessment.can_graduate ? 'Graduate Student' : 'Promote Student' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Academic History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4">Academic History</h3>
                        <div v-if="history.length === 0" class="text-center text-gray-500 py-8">
                            No academic history records.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="record in history"
                                :key="record.id"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span
                                            :class="{
                                                'bg-blue-100 text-blue-800': record.status === 'enrolled',
                                                'bg-green-100 text-green-800': record.status === 'promoted',
                                                'bg-yellow-100 text-yellow-800': record.status === 'repeated',
                                                'bg-purple-100 text-purple-800': record.status === 'graduated',
                                                'bg-red-100 text-red-800': record.status === 'withdrawn',
                                            }"
                                            class="px-2 py-1 rounded text-xs font-medium capitalize"
                                        >
                                            {{ record.status }}
                                        </span>
                                        <h4 class="font-medium mt-2">
                                            Year {{ record.year_level }} - Semester {{ record.semester }}
                                        </h4>
                                        <p class="text-sm text-gray-600">{{ record.academicYear?.name }}</p>
                                        <p v-if="record.notes" class="text-sm text-gray-500 mt-1">{{ record.notes }}</p>
                                    </div>
                                    <div class="text-right text-sm text-gray-500">
                                        <p>{{ record.modules_passed }}/{{ record.modules_enrolled }} modules</p>
                                        <p>{{ new Date(record.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

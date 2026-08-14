<template>
    <Head :title="'Student: ' + student.name" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">{{ student.name }}</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('hive.students.edit', student.id)" class="btn btn-primary">
                            Edit Student
                        </Link>
                    </div>
                </div>

                <!-- Student Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div><strong>Student Number:</strong> {{ student.profile?.student_number ?? '—' }}</div>
                    <div><strong>Email:</strong> {{ student.email }}</div>
                    <div><strong>Date of Birth:</strong> {{ student.profile?.date_of_birth ?? '—' }}</div>
                    <div><strong>Phone:</strong> {{ student.profile?.phone || 'N/A' }}</div>
                    <div class="col-span-2"><strong>Address:</strong> {{ student.profile?.address || 'N/A' }}</div>
                </div>

                <!-- Programme Info -->
                <div class="mb-6" v-if="student.enrollments && student.enrollments.length > 0">
                    <h2 class="text-xl font-semibold mb-3">Programme Enrollment</h2>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2 text-left">Programme</th>
                                <th class="border p-2 text-left">Academic Year</th>
                                <th class="border p-2 text-left">Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="enrollment in student.enrollments" :key="enrollment.id">
                                <td class="border p-2">{{ enrollment.module?.programme?.name ?? 'Unknown Programme' }}</td>
                                <td class="border p-2">{{ enrollment.academic_year ?? '—' }}</td>
                                <td class="border p-2">
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                        {{ enrollment.semester === '1' ? 'SEMESTER 1' : enrollment.semester === '2' ? 'SEMESTER 2' : '—' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PDF Actions -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-3">Documents</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a :href="route('hive.students.generate-proof', student.id)" target="_blank" class="btn btn-success text-center">
                            <i class="fas fa-file-pdf"></i> Proof of Enrolment
                        </a>
                        <a :href="route('hive.students.generate-certificate', student.id)" target="_blank" class="btn btn-warning text-center">
                            <i class="fas fa-file-pdf"></i> Certificate
                        </a>
                        <a :href="route('hive.students.generate-reference', student.id)" target="_blank" class="btn btn-info text-center">
                            <i class="fas fa-file-pdf"></i> Reference Letter
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
    student: {
        type: Object,
        required: true,
    },
});
</script>
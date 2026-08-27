<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    student: Object,
    programme: Object,
    yearLevel: Number,
    semester: String,
    academicYear: Object,
    enrolledModuleIds: Array,
    availableModules: Array,
});

const form = useForm({
    user_id: props.student.id,
    module_id: '',
    academic_year: props.academicYear?.name || new Date().getFullYear().toString(),
    semester: props.semester,
});

const submit = () => {
    form.post(route('hive.enrollment.store'), {
        onSuccess: () => {
            form.reset('module_id');
        },
    });
};
</script>

<template>
    <Head title="Enroll Student" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enroll Student</h2>
                    <p class="text-sm text-gray-600">{{ student.name }} - {{ student.profile?.student_number }}</p>
                </div>
                <a :href="route('hive.enrollment.index')" class="text-indigo-600 hover:text-indigo-900">
                    Back to Enrollments
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Student Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="font-semibold text-lg mb-4">Student Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-medium">{{ student.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Programme</p>
                            <p class="font-medium">{{ programme?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Year Level</p>
                            <p class="font-medium">Year {{ yearLevel }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Semester</p>
                            <p class="font-medium">Semester {{ semester }}</p>
                        </div>
                    </div>
                </div>

                <!-- Enroll in Module -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="font-semibold text-lg mb-4">Enroll in Module</h3>
                    <form @submit.prevent="submit" class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Select Module</label>
                            <select
                                v-model="form.module_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">Choose a module...</option>
                                <option v-for="mod in availableModules" :key="mod.id" :value="mod.id">
                                    {{ mod.code }} - {{ mod.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.module_id" class="text-red-500 text-sm mt-1">{{ form.errors.module_id }}</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                        >
                            Enroll
                        </button>
                    </form>
                </div>

                <!-- Currently Enrolled -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Currently Enrolled Modules</h3>
                    <div v-if="enrolledModuleIds.length === 0" class="text-center text-gray-500 py-4">
                        Not enrolled in any modules for this semester.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="moduleId in enrolledModuleIds"
                            :key="moduleId"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded"
                        >
                            <span class="text-sm">Module ID: {{ moduleId }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

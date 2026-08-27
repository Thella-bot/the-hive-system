<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    modules: Array,
    students: Array,
    academicYear: Object,
    semester: String,
});

const selectedStudents = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');

const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(s =>
        s.name.toLowerCase().includes(query) ||
        s.email.toLowerCase().includes(query)
    );
});

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedStudents.value = filteredStudents.value.map(s => s.id);
    } else {
        selectedStudents.value = [];
    }
};

const form = useForm({
    module_id: '',
    user_ids: [],
    academic_year: props.academicYear?.name || new Date().getFullYear().toString(),
    semester: props.semester,
});

const submit = () => {
    form.user_ids = selectedStudents.value;
    form.post(route('hive.enrollment.bulk-store'), {
        onSuccess: () => {
            selectedStudents.value = [];
        },
    });
};
</script>

<template>
    <Head title="Bulk Enrollment" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bulk Enrollment</h2>
                <a :href="route('hive.enrollment.index')" class="text-indigo-600 hover:text-indigo-900">
                    Back to Enrollments
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Module Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Select Module</label>
                            <select
                                v-model="form.module_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">Choose a module...</option>
                                <option v-for="mod in modules" :key="mod.id" :value="mod.id">
                                    {{ mod.code }} - {{ mod.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.module_id" class="text-red-500 text-sm mt-1">{{ form.errors.module_id }}</p>
                        </div>

                        <!-- Academic Year & Semester -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <input
                                    type="text"
                                    v-model="form.academic_year"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Semester</label>
                                <select
                                    v-model="form.semester"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>

                        <!-- Student Selection -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Students</label>
                                <label class="flex items-center text-sm">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                    />
                                    <span class="ml-2">Select All</span>
                                </label>
                            </div>
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search students..."
                                class="mb-2 block w-full rounded-md border-gray-300 shadow-sm"
                            />
                            <div class="max-h-64 overflow-y-auto border rounded-md">
                                <div
                                    v-for="student in filteredStudents"
                                    :key="student.id"
                                    class="flex items-center p-2 hover:bg-gray-50 border-b last:border-b-0"
                                >
                                    <input
                                        type="checkbox"
                                        :value="student.id"
                                        v-model="selectedStudents"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                    />
                                    <span class="ml-2 text-sm">{{ student.name }}</span>
                                    <span class="ml-2 text-xs text-gray-500">{{ student.email }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ selectedStudents.length }} students selected
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing || selectedStudents.length === 0"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                            >
                                Enroll Selected Students
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    modules: Array,
    enrolledModuleIds: Array,
    enrolledModules: Array,
    pendingRequests: Array,
    semesterContext: Object,
    isRepeatingYear: Boolean,
});

const form = useForm({
    module_id: null,
    reason: '',
});

const isProcessing = (moduleId) => form.processing && form.module_id === moduleId;

const pendingFor = (moduleId, type) =>
    props.pendingRequests.some((r) => r.module_id === moduleId && r.type === type);

/** Submit a request to join a module. Nothing is enrolled until it is approved. */
const requestEnroll = (moduleId) => {
    form.module_id = moduleId;
    form.reason = '';
    form.post(route('hive.enrollment.store'), {
        preserveScroll: true,
    });
};

/** Submit a request to leave a module. The enrollment stays until it is approved. */
const requestLeave = (moduleId) => {
    form.module_id = moduleId;
    form.reason = '';
    form.delete(route('hive.enrollment.destroy', { module: moduleId }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <HiveLayout title="Module Enrollment" description="Request enrollment or deregistration for a module">
        <div class="space-y-6">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
                Enrollments are subject to approval. Submitting a request does not change your
                registration until a member of staff approves it.
            </div>

            <!-- Currently enrolled -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">My Enrolled Modules</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        You can request to be dropped from a module below.
                    </p>
                </div>
                <ul v-if="enrolledModules.length > 0" class="divide-y divide-gray-100">
                    <li v-for="mod in enrolledModules" :key="mod.enrollment_id" class="py-4 flex items-center justify-between px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ mod.code }} - {{ mod.name }}</p>
                            <p class="text-sm text-gray-500">{{ mod.academic_year }} &middot; Semester {{ mod.semester }}</p>
                        </div>
                        <div>
                            <span
                                v-if="pendingFor(mod.module_id, 'deregistration')"
                                class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800"
                            >
                                Drop request pending
                            </span>
                            <SecondaryButton
                                v-else
                                @click="requestLeave(mod.module_id)"
                                :disabled="form.processing"
                                class="text-xs"
                            >
                                Request to Leave
                            </SecondaryButton>
                        </div>
                    </li>
                </ul>
                <div v-else class="text-center py-12">
                    <p class="text-gray-500">You are not enrolled in any modules.</p>
                </div>
            </div>

            <!-- Available modules -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Available Modules</h3>
                    <p v-if="isRepeatingYear" class="mt-1 text-sm text-amber-600">
                        You are viewing modules from your current year and the previous year. Please ensure you request the correct modules for your repeat year.
                    </p>
                    <p v-else-if="semesterContext?.year_level && semesterContext?.semester" class="mt-1 text-sm text-gray-600">
                        Showing modules for Year {{ semesterContext.year_level }}, Semester {{ semesterContext.semester }}.
                    </p>
                    <p v-else class="mt-1 text-sm text-gray-600">
                        Below are the modules available to request.
                    </p>
                </div>
                <ul v-if="modules.length > 0" class="divide-y divide-gray-100">
                    <li v-for="mod in modules" :key="mod.id" class="py-4 flex items-center justify-between px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ mod.code }} - {{ mod.name }}</p>
                            <p class="text-sm text-gray-500">{{ mod.department?.name }}</p>
                        </div>
                        <div>
                            <span
                                v-if="pendingFor(mod.id, 'enrollment')"
                                class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800"
                            >
                                Request pending
                            </span>
                            <PrimaryButton
                                v-else
                                @click="requestEnroll(mod.id)"
                                :disabled="form.processing"
                                class="text-xs"
                            >
                                Request Enrollment
                            </PrimaryButton>
                        </div>
                    </li>
                </ul>
                <div v-else class="text-center py-12">
                    <p v-if="semesterContext?.year_level" class="text-gray-500">
                        There are no modules available for your current semester.
                    </p>
                    <p v-else class="text-gray-500">
                        There are no modules available at this time.
                    </p>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    modules: Array,
    enrolledModuleIds: Array,
});

const form = useForm({
    module_id: null,
});

const isEnrolled = (moduleId) => {
    return props.enrolledModuleIds.includes(moduleId);
};

const enroll = (moduleId) => {
    form.module_id = moduleId;
    form.post(route('hive.enrollment.store'), {
        preserveScroll: true,
    });
};

const leave = (moduleId) => {
    form.module_id = moduleId;
    form.delete(route('hive.enrollment.destroy', { module: moduleId }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <HiveLayout title="Module Enrollment" description="Enroll in available modules">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Available Modules</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Below are the modules available. You can enroll or leave a module at any time.
                </p>
            </div>
            <ul v-if="modules.length > 0" class="divide-y divide-gray-100">
                <li v-for="mod in modules" :key="mod.id" class="py-4 flex items-center justify-between px-6">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ mod.code }} - {{ mod.name }}</p>
                        <p class="text-sm text-gray-500">{{ mod.department?.name }}</p>
                    </div>
                    <div>
                        <div v-if="isEnrolled(mod.id)">
                            <SecondaryButton @click="leave(mod.id)" :disabled="form.processing" class="text-xs">
                                Leave Module
                            </SecondaryButton>
                        </div>
                        <div v-else>
                            <PrimaryButton @click="enroll(mod.id)" :disabled="form.processing" class="text-xs">
                                Enroll
                            </PrimaryButton>
                        </div>
                    </div>
                </li>
            </ul>
            <div v-else class="text-center py-12">
                <p class="text-gray-500">There are no modules available at this time.</p>
            </div>
        </div>
    </HiveLayout>
</template>
